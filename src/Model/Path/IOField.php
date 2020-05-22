<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path;

use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Type;

final class IOField
{
    private string $name;

    private string $type;

    private ?string $pattern;

    /** @var mixed[]|null */
    private ?array $possibleValues;

    /**
     * @param mixed[]|null $possibleValues
     */
    public function __construct(string $name, string $type, ?string $pattern = null, ?array $possibleValues = null)
    {
        Assert::inArray($type, Type::TYPES);

        $this->name           = $name;
        $this->type           = $type;
        $this->pattern        = $pattern;
        $this->possibleValues = $possibleValues;
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

    public function pattern() : ?string
    {
        return $this->pattern;
    }

    /**
     * @return mixed[]|null
     */
    public function possibleValues() : ?array
    {
        return $this->possibleValues;
    }
}
