<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator;

use JMS\Serializer\Serializer;
use Metadata\MetadataFactoryInterface;
use Protung\OpenApiGenerator\Model\ModelRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Builds a generator with every describer the library ships.
 * Describers for kinds of input and output a specification does not use stay idle, so there is no need to leave any of them out.
 */
final class GeneratorFactory
{
    /**
     * @param string                                       $apiVersion          The version documented when the specification does not name one,
     *                                                                          and the version JMS Since/Until annotations are resolved against.
     * @param bool                                         $serializeNull       Whether JMS serializes null values, which decides if nullable
     *                                                                          properties are documented as always present.
     * @param list<class-string<FormTypeInterface<mixed>>> $dictionaryFormTypes Collection form types whose entries are keyed by name rather than
     *                                                                          by position, documented as an object instead of a list.
     */
    public static function create(
        string $apiVersion,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        MetadataFactoryInterface $metadataFactory,
        ValidatorInterface $validator,
        Serializer $jmsSerializer,
        bool $serializeNull = true,
        array $dictionaryFormTypes = [],
    ): Generator {
        $describerFormFactory = new Describer\Form\FormFactory($formFactory);

        $exampleDescriberJms = new Describer\ExampleDescriber\JmsSerializerExampleDescriber($jmsSerializer);
        $exampleDescriber    = new Describer\ExampleDescriber\CompoundExampleDescriber(
            $exampleDescriberJms,
            new Describer\ExampleDescriber\CollectionExampleDescriber($exampleDescriberJms),
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
                                new Describer\ObjectDescriber(
                                    $modelRegistry,
                                    new Describer\ObjectDescriber\PHPBackedEnum(),
                                    new Describer\ObjectDescriber\JMSModel($metadataFactory, $apiVersion, $serializeNull),
                                ),
                                $describerFormFactory,
                                $exampleDescriber,
                                $validator,
                            ),
                        ),
                    ),
                ),
            ),
            new Processor\Definitions($modelRegistry),
        );
    }
}
