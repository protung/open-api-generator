<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Resolver;

use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use Speicher210\OpenApiGenerator\Model\Definition;

use function array_filter;
use function array_key_last;
use function array_map;
use function explode;
use function implode;
use function sprintf;

final class DefinitionName
{
    private const NAMESPACE_SEPARATOR = '\\';

    private function __construct()
    {
    }

    public static function getName(Definition $definition): string
    {
        return sprintf(
            '%s%s',
            self::getNameForClass($definition->className()),
            self::getGroupsSuffix($definition->serializationGroups())
        );
    }

    private static function getNameForClass(string $class): string
    {
        $classParts = explode(self::NAMESPACE_SEPARATOR, $class);

        return $classParts[array_key_last($classParts)];
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
                }
            )
        );

        return implode('', array_map('\ucfirst', explode('-', $groupString)));
    }
}
