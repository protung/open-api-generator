<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model;

use Protung\OpenApiGenerator\Assert\Assert;
use stdClass;

final class Type
{
    public const UNKNOWN = 'unknown';

    public const ANY = 'any';

    public const INTEGER = 'integer';

    public const NUMBER = 'number';

    public const STRING = 'string';

    public const BOOLEAN = 'boolean';

    public const OBJECT = 'object';

    public const ARRAY = 'array';

    public const TYPES = [
        self::UNKNOWN,
        self::ANY,
        self::INTEGER,
        self::NUMBER,
        self::STRING,
        self::BOOLEAN,
        self::OBJECT,
        self::ARRAY,
    ];

    public const SCALAR_TYPES = [
        self::INTEGER,
        self::NUMBER,
        self::STRING,
        self::BOOLEAN,
    ];

    /**
     * @return stdClass|string|list<string>|bool|int|float|null
     *
     * @psalm-pure
     */
    public static function example(string $type): stdClass|string|array|bool|int|float|null
    {
        Assert::inArray($type, self::TYPES);

        return match ($type) {
            self::INTEGER => 123,
            self::NUMBER => 3.14,
            self::STRING => 'string',
            self::BOOLEAN => true,
            self::OBJECT => new stdClass(),
            self::ARRAY => ['array'],
            default => null,
        };
    }
}
