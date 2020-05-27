<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Analyser;

use InvalidArgumentException;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use function array_filter;
use function array_map;
use function array_reduce;
use function array_values;
use function sprintf;

final class PropertyAnalyser
{
    /**
     * @return array<PropertyAnalysisType>
     */
    public function getTypes(string $class, string $propertyName) : array
    {
        $classInfo    = (new BetterReflection())->classReflector()->reflect($class);
        $propertyInfo = $classInfo->getProperty($propertyName);

        if ($propertyInfo === null) {
            throw new InvalidArgumentException(
                sprintf(
                    'Property "%s" does not exist in class "%s".',
                    $propertyName,
                    $class
                )
            );
        }

        $propertyType = $propertyInfo->getType();
        if ($propertyType === null) {
            return $this->getTypesFromDocBlock($propertyInfo);
        }

        if ($propertyType->getName() !== 'array') {
            return [
                PropertyAnalysisSingleType::forSingleValue($propertyType->getName(), $propertyType->allowsNull()),
            ];
        }

        $docBlockTypes = $this->getTypesFromDocBlock($propertyInfo);
        if ($docBlockTypes !== []) {
            return $docBlockTypes;
        }

        return [
            PropertyAnalysisCollectionType::forCollection($propertyType->getName(), $propertyType->allowsNull(), null),
        ];
    }

    /**
     * @return PropertyAnalysisType[]
     */
    private function getTypesFromDocBlock(ReflectionProperty $propertyInfo) : array
    {
        try {
            $docBlockTypes = $propertyInfo->getDocBlockTypes();
        } catch (InvalidArgumentException $throwable) {
            // This might be thrown by the doc block parser.
            return [];
        }

        $alwaysNullable = array_reduce(
            $docBlockTypes,
            static fn(bool $carry, Type $type) => $carry || $type instanceof Null_,
            false
        );

        $docBlockTypes = array_filter(
            $docBlockTypes,
            static fn(Type $type) => ! $type instanceof Null_
        );

        return array_map(
            static function (Type $type) use ($alwaysNullable) : PropertyAnalysisType {
                $actualType = $type instanceof Nullable ? $type->getActualType() : $type;

                if ($actualType instanceof Array_) {
                    if ($actualType->getValueType() instanceof Mixed_) {
                        $elementsType = null;
                    } else {
                        $elementsType = PropertyAnalysisSingleType::forSingleValue((string) $actualType->getValueType(), false);
                    }

                    return PropertyAnalysisCollectionType::forCollection(
                        'array',
                        $alwaysNullable || $type instanceof Nullable,
                        $elementsType
                    );
                }

                return PropertyAnalysisSingleType::forSingleValue(
                    (string) $actualType,
                    $alwaysNullable || $type instanceof Nullable
                );
            },
            array_values($docBlockTypes)
        );
    }
}
