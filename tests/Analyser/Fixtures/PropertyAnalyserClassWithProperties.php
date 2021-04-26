<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Analyser\Fixtures;

use DateTime;
use Generator;

final class PropertyAnalyserClassWithProperties
{
    public $nonDocumented;
    public string $typeHintedString;
    public int $typeHintedInt;
    public float $typeHintedFloat;
    public bool $typeHintedBool;
    public array $typeHintedArray;
    public object $typeHintedObject;
    public ?string $typeHintedStringNullable;
    public ?int $typeHintedIntNullable;
    public ?float $typeHintedFloatNullable;
    public ?bool $typeHintedBoolNullable;
    public ?array $typeHintedArrayNullable;
    public ?object $typeHintedObjectNullable;
    /** @var string */
    public $typeHintedStringDocBlocks;
    /** @var string<class-string> */
    public $typeHintedStringGenericsDocBlocks;
    /** @var class-string<DateTime> */
    public $typeHintedClassStringGenericsDocBlocks;
    /** @var Generator<string> */
    public $typeHintedClassGeneratorOfScalarGenericDocBlocks;
    /** @var Generator<DateTime> */
    public $typeHintedClassGeneratorOfObjectGenericDocBlocks;
    /** @var iterable<string> */
    public $typeHintedClassIterableOfScalarGenericDocBlocks;
    /** @var iterable<DateTime> */
    public $typeHintedClassIterableOfObjectGenericDocBlocks;
    /** @var int */
    public $typeHintedIntDocBlocks;
    /** @var float */
    public $typeHintedFloatDocBlocks;
    /** @var bool */
    public $typeHintedBoolDocBlocks;
    /** @var array */
    public $typeHintedArrayDocBlocks;
    /** @var object */
    public $typeHintedObjectDocBlocks;
    /** @var mixed */
    public $typeHintedMixedDocBlocks;
    /** @var ?string */
    public $typeHintedStringDocBlocksNullable1;
    /** @var ?int */
    public $typeHintedIntDocBlocksNullable1;
    /** @var ?float */
    public $typeHintedFloatDocBlocksNullable1;
    /** @var ?bool */
    public $typeHintedBoolDocBlocksNullable1;
    /** @var ?array */
    public $typeHintedArrayDocBlocksNullable1;
    /** @var ?object */
    public $typeHintedObjectDocBlocksNullable1;
    /** @var ?mixed */
    public $typeHintedMixedDocBlocksNullable1;
    /** @var null|string */
    public $typeHintedStringDocBlocksNullable2;
    /** @var null|int */
    public $typeHintedIntDocBlocksNullable2;
    /** @var null|float */
    public $typeHintedFloatDocBlocksNullable2;
    /** @var null|bool */
    public $typeHintedBoolDocBlocksNullable2;
    /** @var null|array */
    public $typeHintedArrayDocBlocksNullable2;
    /** @var null|object */
    public $typeHintedObjectDocBlocksNullable2;
    /** @var null|mixed */
    public $typeHintedMixedDocBlocksNullable2;
    /** @var string|int|float */
    public $typeHintedUnionDocBlocks;
    /** @var string|int|float|null */
    public $typeHintedUnionDocBlocksNullable;
    /** @var array<string> */
    public $typeHintedArrayOfScalars1;
    /** @var string[] */
    public $typeHintedArrayOfScalars2;
    /** @var array<DateTime> */
    public $typeHintedArrayOfObjects1;
    /** @var DateTime[] */
    public $typeHintedArrayOfObjects2;
    /** @var array<DateTime> */
    public array $typeHintedArrayOfObjects3;

    /**
     * @var int
     * @var string
     */
    public $multipleVarAnnotations;

    /** @psalm-immutable */
    public $nonVarAnnotation;
}
