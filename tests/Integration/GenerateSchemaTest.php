<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration;

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\Builder\DefaultDriverFactory;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Speicher210\OpenApiGenerator\Describer;
use Speicher210\OpenApiGenerator\Generator;
use Speicher210\OpenApiGenerator\Model\ModelRegistry;
use Speicher210\OpenApiGenerator\Processor;
use Speicher210\OpenApiGenerator\Processor\Path;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Validator\ValidatorBuilder;

use function assert;
use function json_encode;

use const JSON_THROW_ON_ERROR;

final class GenerateSchemaTest extends TestCase
{
    private static function createGenerator(string $apiVersion): Generator
    {
        $routes = (new XmlFileLoader(new FileLocator(__DIR__ . '/Fixtures/TestSchemaGeneration/')))->load('routes.xml');

        $validator = (new ValidatorBuilder())->getValidator();

        $formFactory = (new FormFactoryBuilder())
            ->addExtensions(
                [new ValidatorExtension($validator)]
            )
            ->getFormFactory();

        $metadataDirs = [
            'Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS' => __DIR__ . '/Fixtures/TestSchemaGeneration/config/serializer',
        ];

        $jmsSerializer = SerializerBuilder::create()
            ->addMetadataDirs($metadataDirs)
            ->build();
        assert($jmsSerializer instanceof Serializer);

        $describerFormFactory = new Describer\Form\FormFactory($formFactory);

        $exampleDescriberJms        = new Describer\ExampleDescriber\JmsSerializerExampleDescriber($jmsSerializer);
        $exampleDescriberCollection = new Describer\ExampleDescriber\CollectionExampleDescriber($exampleDescriberJms);
        $exampleDescriber           = new Describer\ExampleDescriber\CompoundExampleDescriber(
            $exampleDescriberJms,
            $exampleDescriberCollection
        );

        $formDescriber = new Describer\FormDescriber(
            $describerFormFactory,
            new Describer\Form\SymfonyFormPropertyDescriber(
                new Describer\Form\PropertyDescriber\SymfonyBuiltInPropertyDescriber()
            ),
            new Describer\Form\SymfonyValidatorRequirementsDescriber($validator)
        );

        $modelRegistry = new ModelRegistry();

        return new Generator(
            new Processor\InfoProcessor($apiVersion),
            new Processor\SecurityDefinitions(),
            new Processor\PathsProcessor(
                new Processor\Path\CompoundPathProcessor(
                    new Path\Symfony\PathProcessor(
                        $routes,
                        new Describer\InputDescriber(
                            new Describer\InputDescriber\SimpleInputDescriber(),
                            new Describer\InputDescriber\FormInputDescriber(
                                $formDescriber,
                                $describerFormFactory
                            ),
                        ),
                        new Describer\OutputDescriber(
                            new Describer\ObjectDescriber(
                                $modelRegistry,
                                new Describer\ObjectDescriber\JMSModel(
                                    new MetadataFactory(
                                        (new DefaultDriverFactory(new IdenticalPropertyNamingStrategy()))->createDriver(
                                            $metadataDirs,
                                            new AnnotationReader()
                                        )
                                    ),
                                    $apiVersion
                                ),
                            ),
                            $describerFormFactory,
                            $exampleDescriber
                        )
                    )
                )
            ),
            new Processor\Definitions($modelRegistry)
        );
    }

    public function testSchemaGeneration(): void
    {
        $generator = self::createGenerator('0.0.1');

        $config = require __DIR__ . '/Fixtures/TestSchemaGeneration/definition.php';

        $openApiSpec = $generator->generate($config);

//        file_put_contents(
//            __DIR__ . '/Expected/testSchemaGeneration.json',
//            json_encode($openApiSpec->getSerializableData(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
//        );

        self::assertTrue($openApiSpec->validate());
        self::assertJsonStringEqualsJsonFile(
            __DIR__ . '/Expected/testSchemaGeneration.json',
            json_encode($openApiSpec->getSerializableData(), JSON_THROW_ON_ERROR)
        );
    }
}
