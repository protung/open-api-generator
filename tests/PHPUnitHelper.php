<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests;

use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\TestCase;

use function func_get_args;

/** @require-extends TestCase */
trait PHPUnitHelper
{
    /**
     * @param array<mixed> ...$arguments
     *
     * @return Callback<mixed>
     */
    public static function withConsecutive(array ...$arguments): Callback
    {
        return new Callback(
            static function () use ($arguments): bool {
                /** @var int $call */
                static $call = 0;

                $expected = $arguments[$call] ?? [];

                self::assertEquals($expected, func_get_args());

                $call++;

                return true;
            },
        );
    }
}
