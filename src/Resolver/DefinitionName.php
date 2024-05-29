<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Resolver;

use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use Protung\OpenApiGenerator\Model\Definition;
use Psl;

use function array_filter;
use function array_map;
use function explode;
use function implode;

final class DefinitionName
{
    private const NAMESPACE_SEPARATOR = '\\';

    private function __construct()
    {
    }

    public static function getName(Definition $definition): string
    {
        return Psl\Str\format(
            '%s%s',
            self::getNameForClass($definition->className()),
            self::getGroupsSuffix($definition->serializationGroups()),
        );
    }

    private static function getNameForClass(string $class): string
    {
        return Psl\Type\string()->coerce(Psl\Iter\last(Psl\Str\split($class, self::NAMESPACE_SEPARATOR)));
    }

    /**
     * @param string[] $groups
     */
    private static function getGroupsSuffix(array $groups): string
    {
        $groupString = implode(
            '',
            array_filter(
                $groups,
                static function ($group): bool {
                    return $group !== GroupsExclusionStrategy::DEFAULT_GROUP;
                },
            ),
        );

        return implode('', array_map('\ucfirst', explode('-', $groupString)));
    }
}
