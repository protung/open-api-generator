<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use Speicher210\OpenApiGenerator\Model\Definition;
use Speicher210\OpenApiGenerator\Model\Model;
use Speicher210\OpenApiGenerator\Model\ModelRegistry;
use Speicher210\OpenApiGenerator\Resolver\DefinitionName;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Exclusion\VersionExclusionStrategy;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;

final class JMSModel implements Describer
{
    private const DEFAULT_SERIALIZATION_GROUPS = [GroupsExclusionStrategy::DEFAULT_GROUP];

    private MetadataFactoryInterface $metadataFactory;

    private VersionExclusionStrategy $versionExclusionStrategy;

    private ModelRegistry $modelRegistry;

    private DefinitionName $definitionNameResolver;

    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        ModelRegistry $modelRegistry,
        DefinitionName $definitionNameResolver,
        string $apiVersion
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->modelRegistry = $modelRegistry;
        $this->definitionNameResolver = $definitionNameResolver;
        $this->versionExclusionStrategy = new VersionExclusionStrategy($apiVersion);
    }

    /**
     * @param string[]|null $serializationGroups
     */
    public function describe(string $className, ?array $serializationGroups): Schema
    {
        if ($serializationGroups === null || $serializationGroups === []) {
            $serializationGroups = self::DEFAULT_SERIALIZATION_GROUPS;
        }

        $definition = new Definition($className, $serializationGroups);

        if ($this->modelRegistry->hasModelWithDefinition($definition)) {
            return $this->modelRegistry->getModelWithDefinition($definition)->schema();
//            return new Reference(['$ref' => $this->definitionNameResolver->getReference($definition)]);
        }

        $metadata = $this->getClassMetadata($className);

        $metadataProperties = $this->getPropertiesInSerializationGroups(
            $metadata->propertyMetadata,
            $serializationGroups
        );

        if ($this->hasCustomSerializationGroups($serializationGroups)) {
            if ($this->hasAllPropertiesOnDefaultSerializationGroups($metadataProperties) === true) {
                $definition = new Definition($className, self::DEFAULT_SERIALIZATION_GROUPS);
                if ($this->modelRegistry->hasModelWithDefinition($definition)) {
                    return $this->modelRegistry->getModelWithDefinition($definition)->schema();
//                    return new Reference(['$ref' => $this->definitionNameResolver->getReference($definition)]);
                }
            }
        }

        $definitionSchema = $this->createSchema($className, $serializationGroups);

        $this->modelRegistry->addModel(new Model($definition, $definitionSchema));

        return $definitionSchema;

        return new Reference(['$ref' => $this->definitionNameResolver->getReference($definition)]);
    }

    /**
     * @param string[] $serializationGroups
     */
    private function createSchema(string $className, array $serializationGroups): Schema
    {
        $metadata = $this->getClassMetadata($className);

        $metadataProperties = $this->getPropertiesInSerializationGroups(
            $metadata->propertyMetadata,
            $serializationGroups
        );

        $properties = [];

        foreach ($metadataProperties as $item) {
            // filter properties for not current version
            if ($this->versionExclusionStrategy->shouldSkipProperty($item, SerializationContext::create())) {
                continue;
            }

            if ($item->inline === true) {
                if ($item->type === null || !\array_key_exists('name', $item->type)) {
                    // @todo check types from other sources (doctrine, annotations) ?
                    throw new \LogicException('Inline schema without type defined is not supported.');
                }
                $inlineModel = $this->createSchema($item->type['name'], $serializationGroups);
                foreach ($inlineModel->properties as $name => $property) {
                    $properties[$name] = $property;
                }

                continue;
            }

            $name = $item->serializedName;

            $property = new Schema([]);

            if ($item->type === null) {
                // @todo check types from other sources (doctrine, annotations) ?
                $item->type['name'] = 'string';
            }

            $type = $this->getNestedTypeInArray($item);
            if ($type !== null) {
                $property->type = Type::ARRAY;
                if (!isset($serializationGroups[$name]) || !\is_array($serializationGroups()[$name])) {
                    $groups = $serializationGroups;
                } else {
                    $groups = $serializationGroups[$name];
                }
                $property->items = $this->describe($type, $groups);
            } else {
                $type = $item->type['name'];

                if (\in_array($type, [Type::BOOLEAN, Type::STRING, Type::ARRAY], true)) {
                    $property->type = $type;
                    // Check if field is not a discriminator.
                    if ($name === $metadata->discriminatorFieldName) {
                        if ($metadata->discriminatorValue !== null) {
                            $property->enum = [$metadata->discriminatorValue];
                        } elseif (\count($metadata->discriminatorMap) > 0) {
                            $property->enum = \array_keys($metadata->discriminatorMap);
                        }
                    }
                } elseif (\in_array($type, ['int', 'integer'], true)) {
                    $property->type = Type::INTEGER;
                } elseif (\in_array($type, ['double', 'float'], true)) {
                    $property->type = Type::NUMBER;
                    $property->format = $type;
                } elseif (\in_array($type, ['DateTime', 'DateTimeImmutable'], true)) {
                    $property->type = Type::STRING;
                    $property->format = 'date-time';
                } else {
                    if (!isset($serializationGroups[$name]) || !\is_array($serializationGroups()[$name])) {
                        $groups = $serializationGroups;
                    } else {
                        $groups = $serializationGroups[$name];
                    }
                    $property = $this->describe($type, $groups);
                }
            }

            $properties[$name] = $property;
        }

        if ($this->shouldAddDiscriminatorProperty($metadata)) {
            if (\array_key_exists($metadata->discriminatorFieldName, $properties)) {
                $property = $properties[$metadata->discriminatorFieldName];
            } else {
                $property = new Schema(['type' => Type::STRING]);
            }

            if ($metadata->discriminatorValue !== null) {
                $property->enum = [$metadata->discriminatorValue];
            } elseif (\count($metadata->discriminatorMap) > 0) {
                $property->enum = \array_keys($metadata->discriminatorMap);
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

    private function getNestedTypeInArray(PropertyMetadata $item): ?string
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
    private function shouldAddDiscriminatorProperty(ClassMetadata $metadata): bool
    {
        if ($metadata->discriminatorDisabled) {
            return false;
        }

        if ($metadata->discriminatorBaseClass !== $metadata->name) {
            return false;
        }

        // Check if discriminator was already added as a property.
        return !\array_key_exists($metadata->discriminatorFieldName, $metadata->propertyMetadata);
    }

    /**
     * @param string[] $groups
     */
    private function hasCustomSerializationGroups(array $groups): bool
    {
        return $groups !== self::DEFAULT_SERIALIZATION_GROUPS;
    }

    /**
     * @param PropertyMetadata[] $metadataProperties
     */
    private function hasAllPropertiesOnDefaultSerializationGroups(array $metadataProperties): bool
    {
        foreach ($metadataProperties as $item) {
            if ($item->groups !== null && $item->groups !== [] && $item->groups !== self::DEFAULT_SERIALIZATION_GROUPS) {
                return false;
            }
        }

        return true;
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
        $context = SerializationContext::create();

        return \array_filter(
            $metadataProperties,
            static function (PropertyMetadata $item) use ($groupsExclusion, $context) {
                return !$groupsExclusion->shouldSkipProperty($item, $context);
            }
        );
    }

    private function getClassMetadata(string $className): ClassMetadata
    {
        $metadata = $this->metadataFactory->getMetadataForClass($className);
        if ($metadata === null) {
            throw new \InvalidArgumentException(\sprintf('No metadata found for class %s.', $className));
        }
        if (!$metadata instanceof ClassMetadata) {
            throw new \InvalidArgumentException(
                \sprintf('Expected "%s" class. Got "%s".', ClassMetadata::class, \get_class($metadata))
            );
        }

        return $metadata;
    }
}
