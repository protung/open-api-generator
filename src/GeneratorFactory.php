<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator;

use JMS\Serializer\Serializer;
use Metadata\MetadataFactoryInterface;
use Protung\OpenApiGenerator\Model\ModelRegistry;
use Psl;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Builds a generator with every describer the library ships.
 * Describers for kinds of input and output a specification does not use stay idle, so there is no need to leave any of them out.
 * The JMS Serializer ones are the exception: JMS is optional, and without it objects are described without their properties.
 */
final class GeneratorFactory
{
    /**
     * @param string                                       $apiVersion          The version documented when the specification does not name one,
     *                                                                          and the version JMS Since/Until annotations are resolved against.
     * @param MetadataFactoryInterface|null                $metadataFactory     The JMS Serializer metadata, which describes objects. Pass it
     *                                                                          together with the serializer, or neither.
     * @param Serializer|null                              $jmsSerializer       The JMS Serializer, which turns example objects into examples.
     * @param bool                                         $serializeNull       Whether JMS serializes null values, which decides if nullable
     *                                                                          properties are documented as always present.
     * @param list<class-string<FormTypeInterface<mixed>>> $dictionaryFormTypes Collection form types whose entries are keyed by name rather than
     *                                                                          by position, documented as an object instead of a list.
     */
    public static function create(
        string $apiVersion,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        MetadataFactoryInterface|null $metadataFactory = null,
        Serializer|null $jmsSerializer = null,
        bool $serializeNull = true,
        array $dictionaryFormTypes = [],
    ): Generator {
        Psl\invariant(
            ($metadataFactory === null) === ($jmsSerializer === null),
            'Pass both the JMS Serializer metadata factory and the JMS Serializer, or neither.',
        );

        $objectDescribers        = [];
        $objectExampleDescribers = [];
        if ($metadataFactory !== null && $jmsSerializer !== null) {
            $objectDescribers[]        = new Describer\ObjectDescriber\JMSModel($metadataFactory, $apiVersion, $serializeNull);
            $objectExampleDescribers[] = new Describer\ExampleDescriber\JmsSerializerExampleDescriber($jmsSerializer);
        } else {
            $objectDescribers[] = new Describer\ObjectDescriber\GenericObject();
        }

        $describerFormFactory = new Describer\Form\FormFactory($formFactory);

        $exampleDescriber = new Describer\ExampleDescriber\CompoundExampleDescriber(
            new Describer\ExampleDescriber\CollectionExampleDescriber(...$objectExampleDescribers),
            ...$objectExampleDescribers,
        );

        $formDescriber = new Describer\FormDescriber(
            new Describer\Form\SymfonyFormPropertyDescriber(
                new Describer\Form\PropertyDescriber\DictionaryPropertyDescriber($describerFormFactory, ...$dictionaryFormTypes),
                new Describer\Form\PropertyDescriber\CollectionPropertyDescriber($describerFormFactory),
                new Describer\Form\PropertyDescriber\SymfonyBuiltInPropertyDescriber(),
            ),
            new Describer\Form\SymfonyValidatorRequirementsDescriber($validator),
        );

        $modelRegistry = new ModelRegistry();

        $objectDescriber = new Describer\ObjectDescriber(
            $modelRegistry,
            new Describer\ObjectDescriber\PHPBackedEnum(),
            ...$objectDescribers,
        );

        return new Generator(
            new Processor\InfoProcessor($apiVersion),
            new Processor\SecurityDefinitions(),
            new Processor\PathsProcessor(
                new Processor\Path\CompoundPathProcessor(
                    new Processor\Path\Symfony\PathProcessor(
                        $router->getRouteCollection(),
                        new Describer\OperationDescriber(
                            new Describer\InputDescriber(
                                new Describer\InputDescriber\SymfonyMappedPayloadInputDescriber($validator),
                                new Describer\InputDescriber\SimpleInputDescriber(),
                                new Describer\InputDescriber\FormInputDescriber($formDescriber, $describerFormFactory),
                            ),
                            new Describer\OutputDescriber(
                                $objectDescriber,
                                new Describer\OutputDescriber\ScalarOutputDescriber(),
                                new Describer\OutputDescriber\SimpleOutputDescriber(),
                                new Describer\OutputDescriber\FileOutputDescriber(),
                                new Describer\OutputDescriber\CollectionOutputDescriber($exampleDescriber),
                                new Describer\OutputDescriber\PaginatedOutputDescriber(),
                                new Describer\OutputDescriber\SymfonyFormValidationProblemOutputDescriber($describerFormFactory),
                                new Describer\OutputDescriber\ObjectOutputDescriber($objectDescriber, $exampleDescriber),
                                new Describer\OutputDescriber\SymfonyPayloadValidationProblemOutputDescriber($validator),
                            ),
                        ),
                    ),
                ),
            ),
            new Processor\Definitions($modelRegistry),
        );
    }
}
