<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path;

use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Type;

use function count;

final class IOField
{
    private string $name;

    private string $type;

    private ?string $pattern = null;

    /** @var mixed[]|null */
    private ?array $possibleValues = null;

    /** @var IOField[]|null */
    private ?array $children = null;

    private bool $nullable = false;

    private mixed $example = null;

    private function __construct(string $name, string $type)
    {
        Assert::inArray($type, Type::TYPES);

        $this->name = $name;
        $this->type = $type;
    }

    public static function unknown(string $name): self
    {
        return new self($name, Type::UNKNOWN);
    }

    public static function anything(string $name): self
    {
        return new self($name, Type::ANY);
    }

    public static function stringField(string $name): self
    {
        return new self($name, Type::STRING);
    }

    public static function numberField(string $name): self
    {
        return new self($name, Type::NUMBER);
    }

    public static function integerField(string $name): self
    {
        return new self($name, Type::INTEGER);
    }

    public static function booleanField(string $name): self
    {
        return new self($name, Type::BOOLEAN);
    }

    public static function arrayField(string $name, IOField $element): self
    {
        $self = new self($name, Type::ARRAY);
        $self->withChildren([$element]);

        return $self;
    }

    public static function objectField(string $name, IOField ...$children): self
    {
        $self = new self($name, Type::OBJECT);
        if (count($children) > 0) {
            $self->withChildren($children);
        }

        return $self;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @param IOField[] $children
     */
    public function withChildren(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return IOField[]|null
     */
    public function children(): ?array
    {
        return $this->children;
    }

    public function asNullable(): self
    {
        $this->nullable = true;

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function withPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function pattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @param mixed[] $possibleValues
     */
    public function withPossibleValues(array $possibleValues): self
    {
        $this->possibleValues = $possibleValues;

        return $this;
    }

    /**
     * @return mixed[]|null
     */
    public function possibleValues(): ?array
    {
        return $this->possibleValues;
    }

    public function withExample(mixed $example): self
    {
        $this->example = $example;

        return $this;
    }

    public function example(): mixed
    {
        return $this->example;
    }
}
