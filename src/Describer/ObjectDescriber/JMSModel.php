<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\ObjectDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use InvalidArgumentException;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Exclusion\VersionExclusionStrategy;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\ExpressionPropertyMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use Override;
use Protung\OpenApiGenerator\Analyser\PropertyAnalyser;
use Protung\OpenApiGenerator\Analyser\PropertyAnalysisSingleType;
use Protung\OpenApiGenerator\Analyser\PropertyAnalysisType;
use Protung\OpenApiGenerator\Describer\ObjectDescriber;
use Protung\OpenApiGenerator\Model\Definition;
use Psl;
use RuntimeException;

use function array_key_exists;
use function array_keys;
use function class_exists;
use function count;
use function in_array;
use function is_array;
use function is_string;

final class JMSModel implements Describer
{
    private MetadataFactoryInterface $metadataFactory;

    private VersionExclusionStrategy $versionExclusionStrategy;

    private PropertyAnalyser $propertyAnalyser;

    private bool $serializeNull;

    public function __construct(MetadataFactoryInterface $metadataFactory, string $apiVersion, bool $serializeNull = true)
    {
        $this->metadataFactory          = $metadataFactory;
        $this->versionExclusionStrategy = new VersionExclusionStrategy($apiVersion);
        $this->propertyAnalyser         = new PropertyAnalyser();
        $this->serializeNull            = $serializeNull;
    }

    #[Override]
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
                        $serializationGroups,
                    ),
                    $objectDescriber,
                );
                $childSchemas[] = $childSchema;
            }

            $schema->oneOf = $childSchemas;

            return;
        }

        $propertyMetadata = Psl\Type\vec(Psl\Type\instance_of(PropertyMetadata::class))->coerce($metadata->propertyMetadata);

        $metadataProperties = Psl\Vec\filter(
            $this->getPropertiesInSerializationGroups($propertyMetadata, $serializationGroups),
            // filter properties for not current version
            fn (PropertyMetadata $metadataProperty): bool => ! $this->versionExclusionStrategy->shouldSkipProperty(
                $metadataProperty,
                SerializationContext::create(),
            ),
        );

        $properties = [];

        foreach ($metadataProperties as $metadataProperty) {
            if ($metadataProperty->inline === true) {
                $type = $this->getNestedTypeInArray($metadataProperty);
                if ($type !== null) {
                    if (count($metadataProperties) > 1) {
                        throw new RuntimeException(
                            'Describing of inline array of objects together with other properties is not supported.',
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
                        Psl\Type\shape(['name' => Psl\Type\string()])->coerce($metadataProperty->type)['name'],
                        $serializationGroups,
                    ),
                    $objectDescriber,
                );
                foreach ($inlineModel->properties as $name => $property) {
                    $properties[$name] = Psl\Type\instance_of(Schema::class)->coerce($property);
                }

                continue;
            }

            $type = $this->getNestedTypeInArray($metadataProperty);
            if ($type !== null) {
                $property       = new Schema([]);
                $property->type = Type::ARRAY;
                if (class_exists($type)) {
                    $property->items = $objectDescriber->describe(new Definition($type, $serializationGroups));
                } else {
                    $property->items = match ($type) {
                        'string' => new Schema(['type' => Type::STRING]),
                        'int', 'integer' => new Schema(['type' => Type::INTEGER]),
                        'bool', 'boolean' => new Schema(['type' => Type::BOOLEAN]),
                        'float', 'double' => new Schema(['type' => Type::NUMBER]),
                        default =>  new Schema(['type' => Type::ANY]),
                    };
                }
            } else {
                $propertiesSchemas = Psl\Vec\map(
                    $this->getPropertyTypes($metadataProperty),
                    fn (PropertyAnalysisType $type): Schema => $this->describePropertyInSchema(
                        $type,
                        $metadata,
                        $metadataProperty,
                        $objectDescriber,
                        $serializationGroups,
                    ),
                );

                if (count($propertiesSchemas) > 1) {
                    $property = new Schema(['oneOf' => $propertiesSchemas]);
                } else {
                    $property = Psl\Type\instance_of(Schema::class)->coerce(Psl\Iter\first($propertiesSchemas));
                }
            }

            $properties[Psl\Type\string()->coerce($metadataProperty->serializedName)] = $property;
        }

        $schema->properties = $properties;
        $schema->required   = Psl\Vec\keys(
            Psl\Dict\filter(
                $properties,
                fn (Schema $schema): bool => $this->serializeNull || $schema->nullable !== true, // 'nullable' might be `null` as well (docs are wrong)
            ),
        );
        $schema->type       = Type::OBJECT;
    }

    /**
     * @param list<string> $serializationGroups
     */
    public function describePropertyInSchema(
        PropertyAnalysisType $propertyType,
        ClassMetadata $metadata,
        PropertyMetadata $propertyMetadata,
        ObjectDescriber $objectDescriber,
        array $serializationGroups,
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
            if (($propertyType->parameters()[0] ?? null) === 'Y-m-d') { // As defined by full-date - RFC3339
                $property->format = 'date';
            } else {
                $property->format = 'date-time';
            }
        } elseif ($propertyType->type() === 'enum') {
            $property = $objectDescriber->describe(new Definition(Psl\Type\non_empty_string()->coerce(self::nameOfTypeParameter($propertyType->parameters()[0] ?? null)), $serializationGroups));
        } else {
            $property = $objectDescriber->describe(new Definition($propertyType->type(), $serializationGroups));
        }

        if ($propertyType->nullable()) {
            $property->nullable = true;
        }

        return $property;
    }

    private function getNestedTypeInArray(PropertyMetadata $item): string|null
    {
        $type = self::typeOf($item);
        if ($type === null) {
            return null;
        }

        if ($type['name'] !== 'array' && $type['name'] !== 'ArrayCollection') {
            return null;
        }

        // array<string, MyNamespaceMyObject> or else array<MyNamespaceMyObject>
        return self::nameOfTypeParameter($type['params'][1] ?? null) ?? self::nameOfTypeParameter($type['params'][0] ?? null);
    }

    private static function nameOfTypeParameter(mixed $parameter): string|null
    {
        $name = is_array($parameter) ? ($parameter['name'] ?? null) : null;

        return is_string($name) ? $name : null;
    }

    /**
     * JMS describes the type as an array, whose shape older versions of the library do not declare.
     *
     * @return array{name: string, params: array<mixed>}|null
     */
    private static function typeOf(PropertyMetadata $propertyMetadata): array|null
    {
        if ($propertyMetadata->type === null) {
            return null;
        }

        $type = Psl\Type\dict(Psl\Type\string(), Psl\Type\mixed())->coerce($propertyMetadata->type);

        return [
            'name' => Psl\Type\string()->coerce($type['name'] ?? null),
            'params' => Psl\Type\dict(Psl\Type\array_key(), Psl\Type\mixed())->coerce($type['params'] ?? []),
        ];
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
     * @param list<PropertyMetadata> $metadataProperties
     * @param array<string>          $serializationGroups
     *
     * @return list<PropertyMetadata>
     */
    private function getPropertiesInSerializationGroups(array $metadataProperties, array $serializationGroups): array
    {
        $groupsExclusion = new GroupsExclusionStrategy($serializationGroups);
        $context         = SerializationContext::create();

        return Psl\Vec\filter(
            $metadataProperties,
            static fn (PropertyMetadata $item): bool => ! $groupsExclusion->shouldSkipProperty($item, $context),
        );
    }

    private function getClassMetadata(string $className): ClassMetadata
    {
        $metadata = $this->metadataFactory->getMetadataForClass($className);
        if ($metadata === null) {
            throw new InvalidArgumentException(Psl\Str\format('No metadata found for class %s.', $className));
        }

        if (! $metadata instanceof ClassMetadata) {
            throw new InvalidArgumentException(
                Psl\Str\format('Expected "%s" class. Got "%s".', ClassMetadata::class, $metadata::class),
            );
        }

        return $metadata;
    }

    /**
     * @return array<PropertyAnalysisType>
     */
    private function getPropertyTypes(PropertyMetadata $propertyMetadata): array
    {
        $type = self::typeOf($propertyMetadata);

        $defaultTypes = [
            PropertyAnalysisSingleType::forSingleValue(
                'string',
                false,
                $type['params'] ?? [],
            ),
        ];

        if ($propertyMetadata instanceof VirtualPropertyMetadata || $propertyMetadata instanceof StaticPropertyMetadata || $propertyMetadata instanceof ExpressionPropertyMetadata) {
            if ($type === null) {
                return $defaultTypes;
            }

            return [
                PropertyAnalysisSingleType::forSingleValue(
                    $type['name'],
                    false,
                    $type['params'],
                ),
            ];
        }

        $propertyClass = $propertyMetadata->class;
        Psl\invariant(class_exists($propertyClass), 'Class "%s" declaring property "%s" does not exist.', $propertyClass, $propertyMetadata->name);

        if ($type !== null) {
            return [
                PropertyAnalysisSingleType::forSingleValue(
                    $type['name'],
                    $this->propertyAnalyser->canBeNull($propertyClass, Psl\Type\non_empty_string()->coerce($propertyMetadata->name)),
                    $type['params'],
                ),
            ];
        }

        $types = $this->propertyAnalyser->getTypes($propertyClass, Psl\Type\non_empty_string()->coerce($propertyMetadata->name));
        if (count($types) > 0) {
            return $types;
        }

        return $defaultTypes;
    }

    #[Override]
    public function supports(Definition $definition): bool
    {
        return $this->metadataFactory->getMetadataForClass($definition->className()) !== null;
    }
}
