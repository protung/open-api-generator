<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use cebe\openapi\SpecObjectInterface;
use InvalidArgumentException;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Exclusion\VersionExclusionStrategy;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
use LogicException;
use Metadata\MetadataFactoryInterface;
use Speicher210\OpenApiGenerator\Model\Definition;
use Speicher210\OpenApiGenerator\Model\ModelRegistry;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function count;
use function get_class;
use function in_array;
use function is_array;
use function sprintf;

final class JMSModel implements ObjectDescriber
{
    private MetadataFactoryInterface $metadataFactory;

    private VersionExclusionStrategy $versionExclusionStrategy;

    private ModelRegistry $modelRegistry;

    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        ModelRegistry $modelRegistry,
        string $apiVersion
    ) {
        $this->metadataFactory          = $metadataFactory;
        $this->modelRegistry            = $modelRegistry;
        $this->versionExclusionStrategy = new VersionExclusionStrategy($apiVersion);
    }

    public function describe(Definition $definition) : SpecObjectInterface
    {
        if ($this->modelRegistry->schemaExistsForDefinition($definition)) {
            return $this->modelRegistry->getSchema($definition);
        }

        $this->modelRegistry->addSchema(
            $definition,
            $this->createSchema(
                $definition->className(),
                $definition->serializationGroups()
            )
        );

        return $this->modelRegistry->getSchema($definition);
    }

    /**
     * @param string[] $serializationGroups
     */
    private function createSchema(string $className, array $serializationGroups) : Schema
    {
        $metadata = $this->getClassMetadata($className);

        $metadataProperties = $this->getPropertiesInSerializationGroups(
            $metadata->propertyMetadata,
            $serializationGroups
        );

        $properties = [];

        foreach ($metadataProperties as $metadataProperty) {
            // filter properties for not current version
            if ($this->versionExclusionStrategy->shouldSkipProperty(
                $metadataProperty,
                SerializationContext::create()
            )) {
                continue;
            }

            if ($metadataProperty->inline === true) {
                if ($metadataProperty->type === null || ! array_key_exists('name', $metadataProperty->type)) {
                    // @todo check types from other sources (doctrine, annotations) ?
                    throw new LogicException('Inline schema without type defined is not supported.');
                }

                $inlineModel = $this->createSchema($metadataProperty->type['name'], $serializationGroups);
                foreach ($inlineModel->properties as $name => $property) {
                    $properties[$name] = $property;
                }

                continue;
            }

            $name = $metadataProperty->serializedName;

            $property = new Schema([]);

            if ($metadataProperty->type === null) {
                // @todo check types from other sources (doctrine, annotations) ?
                $metadataProperty->type['name'] = 'string';
            }

            $type = $this->getNestedTypeInArray($metadataProperty);
            if ($type !== null) {
                $property->type = Type::ARRAY;
                if (! isset($serializationGroups[$name]) || ! is_array($serializationGroups()[$name])) {
                    $groups = $serializationGroups;
                } else {
                    $groups = $serializationGroups[$name];
                }

                $property->items = $this->describe(new Definition($type, $groups));
            } else {
                $type = $metadataProperty->type['name'];

                if ($type === Type::STRING) {
                    $property->type = $type;
                    // Check if field is not a discriminator.
                    if ($name === $metadata->discriminatorFieldName) {
                        if ($metadata->discriminatorValue !== null) {
                            $property->enum = [$metadata->discriminatorValue];
                        } elseif (count($metadata->discriminatorMap) > 0) {
                            $property->enum = array_keys($metadata->discriminatorMap);
                        }
                    }
                } elseif ($type === Type::ARRAY) {
                    $property->type = Type::ARRAY;
                } elseif (in_array($type, ['bool', Type::BOOLEAN], true)) {
                    $property->type = Type::BOOLEAN;
                } elseif (in_array($type, ['int', 'integer'], true)) {
                    $property->type = Type::INTEGER;
                } elseif (in_array($type, ['double', 'float'], true)) {
                    $property->type   = Type::NUMBER;
                    $property->format = $type;
                } elseif (in_array($type, ['DateTime', 'DateTimeImmutable'], true)) {
                    $property->type   = Type::STRING;
                    $property->format = 'date-time';
                } else {
                    if (! isset($serializationGroups[$name]) || ! is_array($serializationGroups()[$name])) {
                        $groups = $serializationGroups;
                    } else {
                        $groups = $serializationGroups[$name];
                    }

                    $property = $this->describe(new Definition($type, $groups));
                }
            }

            $properties[$name] = $property;
        }

        if ($this->shouldAddDiscriminatorProperty($metadata)) {
            if (array_key_exists($metadata->discriminatorFieldName, $properties)) {
                $property = $properties[$metadata->discriminatorFieldName];
            } else {
                $property = new Schema(['type' => Type::STRING]);
            }

            if ($metadata->discriminatorValue !== null) {
                $property->enum = [$metadata->discriminatorValue];
            } elseif (count($metadata->discriminatorMap) > 0) {
                $property->enum = array_keys($metadata->discriminatorMap);
            }

            $properties[$metadata->discriminatorFieldName] = $property;
        }

        return new Schema(
            [
                'properties' => $properties,
                'type' => Type::OBJECT,
            ]
        );
    }

    private function getNestedTypeInArray(PropertyMetadata $item) : ?string
    {
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

    /**
     * @todo determine if it is base class and use oneOf functionality if it is so.
     */
    private function shouldAddDiscriminatorProperty(ClassMetadata $metadata) : bool
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
    private function getPropertiesInSerializationGroups(array $metadataProperties, array $serializationGroups) : array
    {
        $groupsExclusion = new GroupsExclusionStrategy($serializationGroups);
        $context         = SerializationContext::create();

        return array_filter(
            $metadataProperties,
            static function (PropertyMetadata $item) use ($groupsExclusion, $context) : bool {
                return ! $groupsExclusion->shouldSkipProperty($item, $context);
            }
        );
    }

    private function getClassMetadata(string $className) : ClassMetadata
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
}
