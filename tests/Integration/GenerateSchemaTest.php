<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration;

use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\Builder\DefaultDriverFactory;
use JMS\Serializer\Metadata\Driver\YamlDriver;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Speicher210\OpenApiGenerator\Describer;
use Speicher210\OpenApiGenerator\Generator;
use Speicher210\OpenApiGenerator\Model\ModelRegistry;
use Speicher210\OpenApiGenerator\Processor;
use Speicher210\OpenApiGenerator\Processor\Path;
use Speicher210\OpenApiGenerator\Resolver\DefinitionName;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\Validator\ValidatorBuilder;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final class GenerateSchemaTest extends TestCase
{
    private static function createGenerator(string $apiVersion) : Generator
    {
        $routes = (new RouteCollectionBuilder(new XmlFileLoader(new FileLocator(__DIR__ . '/Fixtures/TestSchemaGeneration/'))))
            ->import('routes.xml')
            ->build();

        $validator = (new ValidatorBuilder())->getValidator();

        $formFactory = (new FormFactoryBuilder())
            ->addExtensions(
                [new ValidatorExtension($validator)]
            )
            ->getFormFactory();

        $namingStrategy = new IdenticalPropertyNamingStrategy();

        $metadataDirs = [
            'Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model' => __DIR__ . '/Fixtures/TestSchemaGeneration/config/serializer',
        ];

        $metadataFactory = new MetadataFactory(
            (new DefaultDriverFactory($namingStrategy))->createDriver($metadataDirs, new AnnotationReader())
        );

        $jmsDriver = new YamlDriver(new \Metadata\Driver\FileLocator($metadataDirs), $namingStrategy);

        $formDescriber = new Describer\FormDescriber(
            new Describer\Form\FormFactory($formFactory),
            new Describer\Form\SymfonyFormPropertyDescriber(),
            new Describer\Form\SymfonyValidatorRequirementsDescriber($validator)
        );

        $describerFormFactory = new Describer\Form\FormFactory($formFactory);

        $definitionName = new DefinitionName($jmsDriver);

        $modelRegistry = new ModelRegistry($definitionName);

        return new Generator(
            new Processor\InfoProcessor($apiVersion),
            new Processor\SecurityDefinitions(),
            new Processor\PathsProcessor(
                new Processor\Path\CompoundPathProcessor(
                    new Path\Symfony\PathProcessor(
                        $routes,
                        new Describer\InputDescriber(
                            new Describer\Query($formDescriber),
                            new Describer\RequestBodyContent($formDescriber),
                            $describerFormFactory
                        ),
                        new Describer\OutputDescriber(
                            new Describer\ObjectDescriber\JMSModel(
                                $metadataFactory,
                                $modelRegistry,
                                $apiVersion
                            ),
                            $describerFormFactory
                        )
                    )
                )
            ),
            new Processor\Definitions(
                $modelRegistry,
                $definitionName
            )
        );
    }

    public function testSchemaGeneration() : void
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
