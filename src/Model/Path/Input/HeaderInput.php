<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Input;

use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\IOField;
use Speicher210\OpenApiGenerator\Model\Type;

final class HeaderInput extends SimpleInput
{
    private function __construct(IOField ...$fields)
    {
        parent::__construct(self::LOCATION_HEADERS, ...$fields);
    }

    public static function withIOField(IOField $field) : self
    {
        Assert::same($field->type(), Type::STRING, 'Header type must be a string.');

        return new self($field);
    }

    public static function withName(string $name) : self
    {
        return new self(IOField::stringField($name));
    }
}
