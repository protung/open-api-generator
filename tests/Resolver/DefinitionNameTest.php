<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Resolver;

use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Model\Definition;
use Protung\OpenApiGenerator\Resolver\DefinitionName;
use stdClass;

final class DefinitionNameTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProviderTestGetName(): array
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
    public function testGetName(Definition $definition, string $expected): void
    {
        $actual = DefinitionName::getName($definition);

        self::assertSame($expected, $actual);
    }
}
