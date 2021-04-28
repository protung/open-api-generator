<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\ObjectDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use InvalidArgumentException;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Exclusion\VersionExclusionStrategy;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use RuntimeException;
use Speicher210\OpenApiGenerator\Analyser\PropertyAnalyser;
use Speicher210\OpenApiGenerator\Analyser\PropertyAnalysisSingleType;
use Speicher210\OpenApiGenerator\Analyser\PropertyAnalysisType;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Describer\ObjectDescriber;
use Speicher210\OpenApiGenerator\Model\Definition;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function count;
use function get_class;
use function in_array;
use function reset;
use function sprintf;

final class JMSModel implements Describer
{
    private MetadataFactoryInterface $metadataFactory;

    private VersionExclusionStrategy $versionExclusionStrategy;

    private PropertyAnalyser $propertyAnalyser;

    public function __construct(MetadataFactoryInterface $metadataFactory, string $apiVersion)
    {
        $this->metadataFactory          = $metadataFactory;
        $this->versionExclusionStrategy = new VersionExclusionStrategy($apiVersion);
        $this->propertyAnalyser         = new PropertyAnalyser();
    }

    public function describeInSchema(Schema $schema, Definition $definition, ObjectDescriber $objectDescriber): void
    {
        $metadata            = $this->getClassMetadata($definition->className());
        $serializationGroups = $definition->serializationGroups();

        if ($this->isDiscriminatorBaseClass($metadata)) {
            $childSchemas = [];
            foreach ($metadata->discriminatorMap as $childClass) {
                $childSchema = new Schema([]);
                $this->describeInSchema(
                    $childSchema,
                    new Definition(
                        $childClass,
                        $serializationGroups
                    ),
                    $objectDescriber
                );
                $childSchemas[] = $childSchema;
            }

            $schema->oneOf = $childSchemas;

            return;
        }

        $propertyMetadata = $metadata->propertyMetadata;
        Assert::allIsInstanceOf($propertyMetadata, PropertyMetadata::class);
        $metadataProperties = array_filter(
            $this->getPropertiesInSerializationGroups($propertyMetadata, $serializationGroups),
            function (PropertyMetadata $metadataProperty): bool {
                // filter properties for not current version
                return ! $this->versionExclusionStrategy->shouldSkipProperty(
                    $metadataProperty,
                    SerializationContext::create()
                );
            }
        );

        $properties = [];

        foreach ($metadataProperties as $metadataProperty) {
            if ($metadataProperty->inline === true) {
                $type = $this->getNestedTypeInArray($metadataProperty);
                if ($type !== null) {
                    if (count($metadataProperties) > 1) {
                        throw new RuntimeException(
                            'Describing of inline array of objects together with other properties is not supported.'
                        );
                    }

                    $schema->type  = Type::ARRAY;
                    $schema->items = $objectDescriber->describe(new Definition($type, $serializationGroups));

                    return;
                }

                $inlineModel = new Schema([]);
                $this->describeInSchema(
                    $inlineModel,
                    new Definition(
                        $metadataProperty->type['name'],
                        $serializationGroups
                    ),
                    $objectDescriber
                );
                foreach ($inlineModel->properties as $name => $property) {
                    Assert::isInstanceOf($property, Schema::class);
                    $properties[$name] = $property;
                }

                continue;
            }

            $type = $this->getNestedTypeInArray($metadataProperty);
            if ($type !== null) {
                $property        = new Schema([]);
                $property->type  = Type::ARRAY;
                $property->items = $objectDescriber->describe(new Definition($type, $serializationGroups));
            } else {
                $propertiesSchemas = array_map(
                    fn ($type) => $this->describePropertyInSchema(
                        $type,
                        $metadata,
                        $metadataProperty,
                        $objectDescriber,
                        $serializationGroups
                    ),
                    $this->getPropertyTypes($metadataProperty)
                );

                if (count($propertiesSchemas) > 1) {
                    $property = new Schema(['oneOf' => $propertiesSchemas]);
                } else {
                    $property = reset($propertiesSchemas);
                    Assert::isInstanceOf($property, Schema::class);
                }
            }

            $name              = $metadataProperty->serializedName;
            $properties[$name] = $property;
        }

        $schema->properties = $properties;
        $schema->type       = Type::OBJECT;
    }

    /**
     * @param string[] $serializationGroups
     */
    public function describePropertyInSchema(
        PropertyAnalysisType $propertyType,
        ClassMetadata $metadata,
        PropertyMetadata $propertyMetadata,
        ObjectDescriber $objectDescriber,
        array $serializationGroups
    ): Schema {
        $name = $propertyMetadata->serializedName;

        $property = new Schema([]);

        if ($propertyType->type() === Type::STRING) {
            $property->type = $propertyType->type();
            // Check if field is not a discriminator.
            if ($name === $metadata->discriminatorFieldName) {
                if ($metadata->discriminatorValue !== null) {
                    $property->enum = [$metadata->discriminatorValue];
                } elseif (count($metadata->discriminatorMap) > 0) {
                    $property->enum = array_keys($metadata->discriminatorMap);
                }
            }
        } elseif ($propertyType->type() === 'mixed') {
            $property->type = Type::ANY;
        } elseif ($propertyType->type() === Type::ARRAY) {
            $property->type  = Type::ARRAY;
            $property->items = new Schema(['type' => Type::STRING]);
        } elseif (in_array($propertyType->type(), ['bool', Type::BOOLEAN], true)) {
            $property->type = Type::BOOLEAN;
        } elseif (in_array($propertyType->type(), ['int', 'integer'], true)) {
            $property->type = Type::INTEGER;
        } elseif (in_array($propertyType->type(), ['double', 'float'], true)) {
            $property->type   = Type::NUMBER;
            $property->format = $propertyType->type();
        } elseif (in_array($propertyType->type(), ['DateTime', 'DateTimeImmutable', 'DateTimeInterface'], true)) {
            $property->type = Type::STRING;
            if (isset($propertyType->parameters()[0]) && $propertyType->parameters()[0] === 'Y-m-d') { // As defined by full-date - RFC3339
                $property->format = 'date';
            } else {
                $property->format = 'date-time';
            }
        } else {
            $property = $objectDescriber->describe(new Definition($propertyType->type(), $serializationGroups));
        }

        if ($propertyType->nullable() === true) {
            $property->nullable = true;
        }

        return $property;
    }

    private function getNestedTypeInArray(PropertyMetadata $item): ?string
    {
        if ($item->type === null) {
            return null;
        }

        if ($item->type['name'] !== 'array' && $item->type['name'] !== 'ArrayCollection') {
            return null;
        }

        // array<string, MyNamespaceMyObject>
        if (isset($item->type['params'][1]['name'])) {
            return $item->type['params'][1]['name'];
        }

        // array<MyNamespaceMyObject>
        if (isset($item->type['params'][0]['name'])) {
            return $item->type['params'][0]['name'];
        }

        return null;
    }

    private function isDiscriminatorBaseClass(ClassMetadata $metadata): bool
    {
        if ($metadata->discriminatorDisabled) {
            return false;
        }

        if ($metadata->discriminatorBaseClass !== $metadata->name) {
            return false;
        }

        // Check if discriminator was already added as a property.
        return ! array_key_exists($metadata->discriminatorFieldName, $metadata->propertyMetadata);
    }

    /**
     * @param PropertyMetadata[] $metadataProperties
     * @param string[]           $serializationGroups
     *
     * @return PropertyMetadata[]
     */
    private function getPropertiesInSerializationGroups(array $metadataProperties, array $serializationGroups): array
    {
        $groupsExclusion = new GroupsExclusionStrategy($serializationGroups);
        $context         = SerializationContext::create();

        return array_filter(
            $metadataProperties,
            static function (PropertyMetadata $item) use ($groupsExclusion, $context): bool {
                return ! $groupsExclusion->shouldSkipProperty($item, $context);
            }
        );
    }

    private function getClassMetadata(string $className): ClassMetadata
    {
        $metadata = $this->metadataFactory->getMetadataForClass($className);
        if ($metadata === null) {
            throw new InvalidArgumentException(sprintf('No metadata found for class %s.', $className));
        }

        if (! $metadata instanceof ClassMetadata) {
            throw new InvalidArgumentException(
                sprintf('Expected "%s" class. Got "%s".', ClassMetadata::class, get_class($metadata))
            );
        }

        return $metadata;
    }

    /**
     * @return array<PropertyAnalysisType>
     */
    private function getPropertyTypes(PropertyMetadata $propertyMetadata): array
    {
        $defaultTypes = [
            PropertyAnalysisSingleType::forSingleValue(
                'string',
                false,
                $propertyMetadata->type['params'] ?? []
            ),
        ];

        if ($propertyMetadata instanceof VirtualPropertyMetadata || $propertyMetadata instanceof StaticPropertyMetadata) {
            if ($propertyMetadata->type === null) {
                return $defaultTypes;
            }

            return [
                PropertyAnalysisSingleType::forSingleValue(
                    $propertyMetadata->type['name'],
                    false,
                    $propertyMetadata->type['params'] ?? []
                ),
            ];
        }

        $propertyClass = $propertyMetadata->class;
        Assert::classExists($propertyClass);

        if ($propertyMetadata->type !== null) {
            return [
                PropertyAnalysisSingleType::forSingleValue(
                    $propertyMetadata->type['name'],
                    $this->propertyAnalyser->canBeNull($propertyClass, $propertyMetadata->name),
                    $propertyMetadata->type['params']
                ),
            ];
        }

        $types = $this->propertyAnalyser->getTypes($propertyClass, $propertyMetadata->name);
        if (count($types) > 0) {
            return $types;
        }

        return $defaultTypes;
    }

    public function supports(Definition $definition): bool
    {
        return $this->metadataFactory->getMetadataForClass($definition->className()) !== null;
    }
}
