<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Model\Path\Output;

use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Path\Output\SimpleOutput;
use Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\Enum\StringBackedEnum;

final class SimpleOutputTest extends TestCase
{
    public function testExampleForArrayOfScalarsIsAList(): void
    {
        $output = SimpleOutput::fromIOFields(
            IOField::stringField('label'),
            IOField::arrayField('names', IOField::stringField('name')),
        );

        self::assertSame(
            ['label' => 'string', 'names' => ['string']],
            $output->example(),
        );
    }

    public function testExampleForArrayOfBackedEnumsIsAList(): void
    {
        $output = SimpleOutput::fromIOFields(
            IOField::arrayField('capabilities', IOField::backedEnum('capability', StringBackedEnum::class)),
        );

        self::assertSame(
            ['capabilities' => ['A']],
            $output->example(),
        );
    }

    public function testExampleForArrayOfObjectsIsAListOfObjects(): void
    {
        $output = SimpleOutput::fromIOFields(
            IOField::arrayField(
                'items',
                IOField::objectField(
                    'item',
                    IOField::integerField('id'),
                    IOField::stringField('name'),
                ),
            ),
        );

        self::assertSame(
            ['items' => [['id' => 123, 'name' => 'string']]],
            $output->example(),
        );
    }

    public function testExampleForObjectStaysAKeyedObject(): void
    {
        $output = SimpleOutput::fromIOFields(
            IOField::objectField(
                'item',
                IOField::integerField('id'),
                IOField::arrayField('tags', IOField::stringField('tag')),
            ),
        );

        self::assertSame(
            ['item' => ['id' => 123, 'tags' => ['string']]],
            $output->example(),
        );
    }

    public function testExplicitExampleOnALeafFieldWins(): void
    {
        $output = SimpleOutput::fromIOFields(
            IOField::stringField('label')->withExample('a readable label'),
            IOField::backedEnum('capability', StringBackedEnum::class)->withExample('C'),
        );

        self::assertSame(
            ['label' => 'a readable label', 'capability' => 'C'],
            $output->example(),
        );
    }

    public function testExplicitExampleOnAnArrayFieldWins(): void
    {
        $output = SimpleOutput::fromIOFields(
            IOField::arrayField('capabilities', IOField::backedEnum('capability', StringBackedEnum::class))
                ->withExample(['B', 'C']),
        );

        self::assertSame(
            ['capabilities' => ['B', 'C']],
            $output->example(),
        );
    }

    public function testExplicitExampleOnAnObjectFieldWins(): void
    {
        $output = SimpleOutput::fromIOFields(
            IOField::objectField(
                'item',
                IOField::integerField('id'),
                IOField::stringField('name'),
            )->withExample(['id' => 7, 'name' => 'seven']),
        );

        self::assertSame(
            ['item' => ['id' => 7, 'name' => 'seven']],
            $output->example(),
        );
    }
}
