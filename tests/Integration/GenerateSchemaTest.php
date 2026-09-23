<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration;

use JMS\Serializer\Builder\DefaultDriverFactory;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\SerializerBuilder;
use Metadata\MetadataFactory;
use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Generator;
use Protung\OpenApiGenerator\GeneratorFactory;
use Protung\OpenApiGenerator\Model\Specification;
use Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form\TestDictionaryType;
use Psl;
use Psl\Json;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\ValidatorBuilder;

use function file_put_contents;
use function getenv;

final class GenerateSchemaTest extends TestCase
{
    private static function createGenerator(string $apiVersion): Generator
    {
        $validator = (new ValidatorBuilder())->enableAttributeMapping()->getValidator();

        $formFactory = (new FormFactoryBuilder())
            ->addExtensions(
                [new ValidatorExtension($validator)],
            )
            ->getFormFactory();

        $metadataDirs = [
            'Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS' => __DIR__ . '/Fixtures/TestSchemaGeneration/config/serializer',
        ];

        // @todo use EnumPropertiesDriver as the driver to simulate JMSSerializerBundle (@see https://github.com/schmittjoh/JMSSerializerBundle/pull/919)
        $jmsSerializerBuilder = SerializerBuilder::create();
        $jmsSerializerBuilder->addMetadataDirs($metadataDirs);
        $jmsSerializerBuilder->enableEnumSupport(true);

        return GeneratorFactory::create(
            $apiVersion,
            new Router(new YamlFileLoader(new FileLocator(__DIR__ . '/Fixtures/TestSchemaGeneration/')), 'routes.yaml'),
            $formFactory,
            new MetadataFactory(
                (new DefaultDriverFactory(new IdenticalPropertyNamingStrategy()))->createDriver($metadataDirs),
            ),
            $validator,
            $jmsSerializerBuilder->build(),
            serializeNull: false,
            dictionaryFormTypes: [TestDictionaryType::class],
        );
    }

    public function testSchemaGeneration(): void
    {
        $generator = self::createGenerator('0.0.1');

        $config = Psl\Type\instance_of(Specification::class)->coerce(
            require __DIR__ . '/Fixtures/TestSchemaGeneration/definition.php',
        );

        $openApiSpec = $generator->generate($config);

        // Regenerate the expected output with `just update-snapshots`, then review the diff before committing it.
        if (getenv('UPDATE_SNAPSHOTS') === '1') {
            file_put_contents(
                __DIR__ . '/Expected/testSchemaGeneration.json',
                Json\encode($openApiSpec->getSerializableData(), true),
            );
        }

        self::assertTrue($openApiSpec->validate());
        // Compared as text, since a JSON comparison would ignore the order of the keys in the generated document.
        self::assertStringEqualsFile(
            __DIR__ . '/Expected/testSchemaGeneration.json',
            Json\encode($openApiSpec->getSerializableData(), true),
        );
    }
}
