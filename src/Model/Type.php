<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model;

use stdClass;

enum Type: string
{
    case Unknown = 'unknown';

    case Any = 'any';

    case Integer = 'integer';

    case Number = 'number';

    case String = 'string';

    case Boolean = 'boolean';

    case Object = 'object';

    case Array = 'array';

    public function isScalar(): bool
    {
        return match ($this) {
            self::Integer, self::Number, self::String, self::Boolean => true,
            default => false,
        };
    }

    /**
     * @return stdClass|string|list<string>|bool|int|float|null
     */
    public function example(): stdClass|string|array|bool|int|float|null
    {
        return match ($this) {
            self::Integer => 123,
            self::Number => 3.14,
            self::String => 'string',
            self::Boolean => true,
            self::Object => new stdClass(),
            self::Array => ['array'],
            default => null,
        };
    }
}
