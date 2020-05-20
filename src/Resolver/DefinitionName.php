<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Resolver;

use Speicher210\OpenApiGenerator\Model\Definition;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use Metadata\Driver\AdvancedFileLocatorInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class DefinitionName
{
    private const NAMESPACE_SEPARATOR = '\\';

    private const DEFINITIONS_REFERENCE_PREFIX = '#/components/schemas/';

    private const DEFAULT_PREFIXES_TO_IGNORE = ['App', 'Entity', 'Model'];

    private AdvancedFileLocatorInterface $fileLocator;

    private NameConverterInterface $nameConverter;

    /** @var array<string,string> */
    private array $classToDefinitionMap = [];

    private bool $classToDefinitionMapBuilt = false;

    /** @var string[] */
    private array $prefixesToIgnore;

    public function __construct(
        AdvancedFileLocatorInterface $fileLocator,
        ?NameConverterInterface $nameConverter = null,
        array $prefixesToIgnore = self::DEFAULT_PREFIXES_TO_IGNORE
    ) {
        $this->fileLocator = $fileLocator;
        $this->nameConverter = $nameConverter ?? new CamelCaseToSnakeCaseNameConverter(null, false);
        $this->prefixesToIgnore = $prefixesToIgnore;
    }

    public function getName(Definition $definition): string
    {
        return \sprintf(
            '%s%s',
            $this->getNameForClass($definition->className()),
            $this->getGroupsSuffix($definition->serializationGroups())
        );
    }

    public function getReference(Definition $definition): string
    {
        return self::DEFINITIONS_REFERENCE_PREFIX . $this->getName($definition);
    }

    private function getNameForClass(string $class): string
    {
        if (!$this->classToDefinitionMapBuilt) {
            $this->buildMapBetweenClassesAndDescriptionNames();
        }

        if (!\array_key_exists($class, $this->classToDefinitionMap)) {
            // @todo build name for class not in a map.
            throw new \RuntimeException('Cannot determine definition name for class "%s".');
        }

        return $this->classToDefinitionMap[$class];
    }

    private function getGroupsSuffix(array $groups): string
    {
        $groupString = \implode(
            '',
            \array_filter(
                $groups,
                static function ($group) {
                    return $group !== GroupsExclusionStrategy::DEFAULT_GROUP;
                }
            )
        );

        return $this->nameConverter->denormalize(\str_replace('-', '_', $groupString));
    }

    private function buildMapBetweenClassesAndDescriptionNames(): void
    {
        foreach ($this->fileLocator->findAllClasses('yml') as $class) {
            $classInfo = new \ReflectionClass($class);
            $shortName = $classInfo->getShortName();
            $prefixes = \array_filter(
                \explode(self::NAMESPACE_SEPARATOR, $classInfo->getNamespaceName()),
                function (string $prefix) use ($shortName): bool {
                    if (\in_array($prefix, $this->prefixesToIgnore, true)) {
                        return false;
                    }

                    // We don't want names like "PurchaseOrderPurchaseOrder".
                    return \strpos($shortName, $prefix) !== 0;
                }
            );

            $name = \implode('', $prefixes) . $shortName;

            if (\in_array($name, $this->classToDefinitionMap, true)) {
                throw new \RuntimeException(\sprintf('Cannot determine unique name for class "%s".', $name));
            }
            $this->classToDefinitionMap[$class] = $name;
        }

        $this->classToDefinitionMapBuilt = true;
    }
}
