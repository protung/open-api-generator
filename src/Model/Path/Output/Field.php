<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Assert\Assertion;
use Speicher210\OpenApiGenerator\Model\Type;

final class Field
{
    private string $name;

    private string $type;

    public function __construct(string $name, string $type)
    {
        Assertion::inArray($type, Type::TYPES);

        $this->name = $name;
        $this->type = $type;
    }

    public static function stringField(string $name) : self
    {
        return new self($name, Type::STRING);
    }

    public static function integerField(string $name) : self
    {
        return new self($name, Type::INTEGER);
    }

    public function name() : string
    {
        return $this->name;
    }

    public function type() : string
    {
        return $this->type;
    }
}
