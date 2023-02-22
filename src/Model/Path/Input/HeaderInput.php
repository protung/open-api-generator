<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Input;

use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Model\Path\InputLocation;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Type;

final class HeaderInput extends SimpleInput
{
    private function __construct(IOField ...$fields)
    {
        parent::__construct(InputLocation::Header, ...$fields);
    }

    public static function withIOField(IOField $field): self
    {
        Assert::same($field->type(), Type::String, 'Header type must be a string.');

        return new self($field);
    }

    public static function withName(string $name): self
    {
        return new self(IOField::stringField($name));
    }
}
