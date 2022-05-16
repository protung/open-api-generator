<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Analyser;

use InvalidArgumentException;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Psl\Iter;
use Psl\Str;
use Psl\Type;
use Psl\Vec;
use ReflectionClass;
use ReflectionNamedType;

use function count;
use function in_array;

final class PropertyAnalyser
{
    /**
     * @param class-string $class
     */
    public function canBeNull(string $class, string $propertyName): bool
    {
        $types = $this->getTypes($class, $propertyName);

        foreach ($types as $type) {
            if ($type->nullable() === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param class-string $class
     *
     * @return array<PropertyAnalysisType>
     */
    public function getTypes(string $class, string $propertyName): array
    {
        $reflection = new ReflectionClass($class);

        if (! $reflection->hasProperty($propertyName)) {
            throw new InvalidArgumentException(
                Str\format(
                    'Property "%s" does not exist in class "%s".',
                    $propertyName,
                    $class
                )
            );
        }

        $property = $reflection->getProperty($propertyName);

        // Property has no native type
        if ($property->getType() === null) {
            if ($property->getDocComment() !== false) {
                return $this->parseDocType($property->getDocComment());
            }

            return [PropertyAnalysisSingleType::forSingleMixedValue()];
        }

        $propertyType = Type\instance_of(ReflectionNamedType::class)->coerce($property->getType());

        // Property has scalar native type
        if ($propertyType->getName() !== 'array') {
            return [PropertyAnalysisSingleType::forSingleValue($propertyType->getName(), $propertyType->allowsNull(), [])];
        }

        // Property has doc comment
        if ($property->getDocComment() !== false) {
            return $this->parseDocType($property->getDocComment());
        }

        // Property has native array type
        return [PropertyAnalysisCollectionType::forCollection('array', $propertyType->allowsNull(), null)];
    }

    /**
     * @return array<PropertyAnalysisType>
     */
    private function parseDocType(string $docComment): array
    {
        $parser    = $this->getParser();
        $lexer     = new Lexer();
        $comment   = $parser->parse(new TokenIterator($lexer->tokenize($docComment)));
        $varValues = $comment->getVarTagValues();
        if (count($varValues) > 1) {
            throw new InvalidArgumentException('Doc comment cannot have more than one @var annotation.');
        }

        // docblock without @var annotation
        if (count($varValues) === 0) {
            return [PropertyAnalysisSingleType::forSingleMixedValue()];
        }

        $value    = $varValues[0];
        $type     = $value->type;
        $nullable = false;

        // Nullable type
        if ($type instanceof NullableTypeNode) {
            $nullable = true;
            $type     = $type->type;
        }

        // generic <> docblock
        if ($type instanceof GenericTypeNode) {
            return $this->parseGenericTypeNode($type, $nullable);
        }

        // array[] docblock
        if ($type instanceof ArrayTypeNode) {
            return $this->parseArrayTypeNode($type, $nullable);
        }

        // Union or nullable
        if ($type instanceof UnionTypeNode) {
            return $this->parseUnionType($type);
        }

        $type = Type\instance_of(IdentifierTypeNode::class)->coerce($type);

        if ($type->name === 'array') {
            return [
                PropertyAnalysisCollectionType::forCollection(
                    'array',
                    $nullable,
                    null
                ),
            ];
        }

        return [PropertyAnalysisSingleType::forSingleValue($type->name, $nullable, [])];
    }

    /**
     * @return array<PropertyAnalysisType>
     */
    private function parseGenericTypeNode(GenericTypeNode $type, bool $nullable): array
    {
        $genericType = Type\instance_of(IdentifierTypeNode::class)->coerce($type->genericTypes[0]);

        if (in_array($type->type->name, ['array', 'Generator', 'iterable'], true)) {
            return [
                PropertyAnalysisCollectionType::forCollection(
                    'array',
                    $nullable,
                    PropertyAnalysisSingleType::forSingleValue($genericType->name, $nullable, [])
                ),
            ];
        }

        return [PropertyAnalysisSingleType::forSingleValue('string', $nullable, [])];
    }

    /**
     * @return array<PropertyAnalysisType>
     */
    private function parseArrayTypeNode(ArrayTypeNode $type, bool $nullable): array
    {
        $identifierType = Type\instance_of(IdentifierTypeNode::class)->coerce($type->type);

        return [
            PropertyAnalysisCollectionType::forCollection(
                'array',
                $nullable,
                PropertyAnalysisSingleType::forSingleValue($identifierType->name, $nullable, [])
            ),
        ];
    }

    /**
     * @return array<PropertyAnalysisType>
     */
    private function parseUnionType(UnionTypeNode $type): array
    {
        $types = Type\vec(Type\instance_of(IdentifierTypeNode::class))->coerce($type->types);

        $nullable   = Iter\any($types, static fn (IdentifierTypeNode $type): bool => $type->name === 'null');
        $unionTypes = Vec\filter($types, static fn (IdentifierTypeNode $type): bool => $type->name !== 'null');

        return Vec\map(
            $unionTypes,
            static function (IdentifierTypeNode $type) use ($nullable) {
                if ($type->name === 'array') {
                    return PropertyAnalysisCollectionType::forCollection('array', $nullable, null);
                }

                return PropertyAnalysisSingleType::forSingleValue($type->name, $nullable, []);
            }
        );
    }

    private function getParser(): PhpDocParser
    {
        return new PhpDocParser(
            new TypeParser(
                new ConstExprParser()
            ),
            new ConstExprParser()
        );
    }
}
