<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use Speicher210\OpenApiGenerator\Assert\Assert;
use stdClass;

final class Type
{
    public const ANY = 'any';

    public const INTEGER = 'integer';

    public const NUMBER = 'number';

    public const STRING = 'string';

    public const BOOLEAN = 'boolean';

    public const OBJECT = 'object';

    public const ARRAY = 'array';

    public const TYPES = [
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
     * @return mixed
     */
    public static function example(string $type)
    {
        Assert::inArray($type, self::TYPES);

        switch ($type) {
            case self::INTEGER:
                return 123;

            case self::NUMBER:
                return 3.14;

            case self::STRING:
                return 'string';

            case self::BOOLEAN:
                return true;

            case self::OBJECT:
                return new stdClass();

            case self::ARRAY:
                return ['array'];

            default:
                return null;
        }
    }
}
