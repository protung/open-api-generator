<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Analyser;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Speicher210\OpenApiGenerator\Analyser\PropertyAnalyser;
use Speicher210\OpenApiGenerator\Analyser\PropertyAnalysisCollectionType;
use Speicher210\OpenApiGenerator\Analyser\PropertyAnalysisSingleType;
use Speicher210\OpenApiGenerator\Analyser\PropertyAnalysisType;
use Speicher210\OpenApiGenerator\Tests\Analyser\Fixtures\PropertyAnalyserClassWithProperties;
use stdClass;

final class PropertyAnalyserTest extends TestCase
{
    public function testGetPropertyTypeThrowsExceptionIfPropertyDoesNotExist(): void
    {
        $propertyAnalyser = new PropertyAnalyser();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Property "test" does not exist in class "stdClass".');

        $propertyAnalyser->getTypes(stdClass::class, 'test');
    }

    /**
     * @return mixed[]
     */
    public static function dataProviderTestGetPropertyType(): array
    {
        return [
            ['nonDocumented', []],
            ['typeHintedString', [PropertyAnalysisSingleType::forSingleValue('string', false)]],
            ['typeHintedInt', [PropertyAnalysisSingleType::forSingleValue('int', false)]],
            ['typeHintedFloat', [PropertyAnalysisSingleType::forSingleValue('float', false)]],
            ['typeHintedBool', [PropertyAnalysisSingleType::forSingleValue('bool', false)]],
            ['typeHintedArray', [PropertyAnalysisCollectionType::forCollection('array', false, null)]],
            ['typeHintedObject', [PropertyAnalysisSingleType::forSingleValue('object', false)]],
            ['typeHintedStringNullable', [PropertyAnalysisSingleType::forSingleValue('string', true)]],
            ['typeHintedIntNullable', [PropertyAnalysisSingleType::forSingleValue('int', true)]],
            ['typeHintedFloatNullable', [PropertyAnalysisSingleType::forSingleValue('float', true)]],
            ['typeHintedBoolNullable', [PropertyAnalysisSingleType::forSingleValue('bool', true)]],
            ['typeHintedArrayNullable', [PropertyAnalysisCollectionType::forCollection('array', true, null)]],
            ['typeHintedObjectNullable', [PropertyAnalysisSingleType::forSingleValue('object', true)]],
            ['typeHintedStringDocBlocks', [PropertyAnalysisSingleType::forSingleValue('string', false)]],
            ['typeHintedIntDocBlocks', [PropertyAnalysisSingleType::forSingleValue('int', false)]],
            ['typeHintedFloatDocBlocks', [PropertyAnalysisSingleType::forSingleValue('float', false)]],
            ['typeHintedBoolDocBlocks', [PropertyAnalysisSingleType::forSingleValue('bool', false)]],
            ['typeHintedArrayDocBlocks', [PropertyAnalysisCollectionType::forCollection('array', false, null)]],
            ['typeHintedObjectDocBlocks', [PropertyAnalysisSingleType::forSingleValue('object', false)]],
            ['typeHintedMixedDocBlocks', [PropertyAnalysisSingleType::forSingleValue('mixed', false)]],
            ['typeHintedStringDocBlocksNullable1', [PropertyAnalysisSingleType::forSingleValue('string', true)]],
            ['typeHintedIntDocBlocksNullable1', [PropertyAnalysisSingleType::forSingleValue('int', true)]],
            ['typeHintedFloatDocBlocksNullable1', [PropertyAnalysisSingleType::forSingleValue('float', true)]],
            ['typeHintedBoolDocBlocksNullable1', [PropertyAnalysisSingleType::forSingleValue('bool', true)]],
            ['typeHintedArrayDocBlocksNullable1', [PropertyAnalysisCollectionType::forCollection('array', true, null)]],
            ['typeHintedObjectDocBlocksNullable1', [PropertyAnalysisSingleType::forSingleValue('object', true)]],
            ['typeHintedMixedDocBlocksNullable1', [PropertyAnalysisSingleType::forSingleValue('mixed', true)]],
            ['typeHintedStringDocBlocksNullable2', [PropertyAnalysisSingleType::forSingleValue('string', true)]],
            ['typeHintedIntDocBlocksNullable2', [PropertyAnalysisSingleType::forSingleValue('int', true)]],
            ['typeHintedFloatDocBlocksNullable2', [PropertyAnalysisSingleType::forSingleValue('float', true)]],
            ['typeHintedBoolDocBlocksNullable2', [PropertyAnalysisSingleType::forSingleValue('bool', true)]],
            ['typeHintedArrayDocBlocksNullable2', [PropertyAnalysisCollectionType::forCollection('array', true, null)]],
            ['typeHintedObjectDocBlocksNullable2', [PropertyAnalysisSingleType::forSingleValue('object', true)]],
            ['typeHintedMixedDocBlocksNullable2', [PropertyAnalysisSingleType::forSingleValue('mixed', true)]],
            [
                'typeHintedUnionDocBlocks',
                [
                    PropertyAnalysisSingleType::forSingleValue('string', false),
                    PropertyAnalysisSingleType::forSingleValue('int', false),
                    PropertyAnalysisSingleType::forSingleValue('float', false),
                ],
            ],
            [
                'typeHintedUnionDocBlocksNullable',
                [
                    PropertyAnalysisSingleType::forSingleValue('string', true),
                    PropertyAnalysisSingleType::forSingleValue('int', true),
                    PropertyAnalysisSingleType::forSingleValue('float', true),
                ],
            ],
            [
                'typeHintedUnionDocBlocksSelectiveNullable',
                [
                    PropertyAnalysisSingleType::forSingleValue('string', true),
                    PropertyAnalysisSingleType::forSingleValue('int', false),
                    PropertyAnalysisSingleType::forSingleValue('float', false),
                ],
            ],

            [
                'typeHintedArrayOfScalars1',
                [
                    PropertyAnalysisCollectionType::forCollection('array', false, PropertyAnalysisSingleType::forSingleValue('string', false)),
                ],
            ],
            [
                'typeHintedArrayOfScalars2',
                [
                    PropertyAnalysisCollectionType::forCollection('array', false, PropertyAnalysisSingleType::forSingleValue('string', false)),
                ],
            ],
            [
                'typeHintedArrayOfObjects1',
                [
                    PropertyAnalysisCollectionType::forCollection('array', false, PropertyAnalysisSingleType::forSingleValue('\DateTime', false)),
                ],
            ],
            [
                'typeHintedArrayOfObjects2',
                [
                    PropertyAnalysisCollectionType::forCollection('array', false, PropertyAnalysisSingleType::forSingleValue('\DateTime', false)),
                ],
            ],
            [
                'typeHintedArrayOfObjects3',
                [
                    PropertyAnalysisCollectionType::forCollection('array', false, PropertyAnalysisSingleType::forSingleValue('\DateTime', false)),
                ],
            ],
        ];
    }

    /**
     * @param PropertyAnalysisType[] $expected
     *
     * @dataProvider dataProviderTestGetPropertyType
     */
    public function testGetPropertyType(string $propertyName, array $expected): void
    {
        $propertyAnalyser = new PropertyAnalyser();
        $actual           = $propertyAnalyser->getTypes(PropertyAnalyserClassWithProperties::class, $propertyName);

        self::assertEquals($expected, $actual);
    }
}
