<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Resolver;

use PHPUnit\Framework\TestCase;
use Speicher210\OpenApiGenerator\Model\Definition;
use Speicher210\OpenApiGenerator\Resolver\DefinitionName;
use stdClass;

final class DefinitionNameTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProviderTestGetName() : array
    {
        return [
            [new Definition(stdClass::class, []), 'stdClass'],
            [new Definition(self::class, []), 'DefinitionNameTest'],
            [new Definition('NonExistingNamespace\NonExistingClass', []), 'NonExistingClass'],
            [new Definition('WithSerializationGroup', ['Test']), 'WithSerializationGroupTest'],
            [new Definition('WithSerializationGroups', ['TestA', 'TestB']), 'WithSerializationGroupsTestATestB'],
        ];
    }

    /**
     * @dataProvider dataProviderTestGetName
     */
    public function testGetName(Definition $definition, string $expected) : void
    {
        $actual = DefinitionName::getName($definition);

        self::assertSame($expected, $actual);
    }
}
