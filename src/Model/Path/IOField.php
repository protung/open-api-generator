<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path;

use BackedEnum;
use InvalidArgumentException;
use Protung\OpenApiGenerator\Model\Type;
use Psl\Vec;
use ReflectionEnum;

use function count;
use function is_subclass_of;

final class IOField
{
    private string $name;

    private Type $type;

    private string|null $pattern = null;

    /** @var mixed[]|null */
    private array|null $possibleValues = null;

    /** @var list<IOField>|null */
    private array|null $children = null;

    private bool $nullable = false;

    private mixed $example = null;

    private bool $required = true;

    private function __construct(string $name, Type $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function unknown(string $name): self
    {
        return new self($name, Type::Unknown);
    }

    public static function anything(string $name): self
    {
        return new self($name, Type::Any);
    }

    public static function stringField(string $name): self
    {
        return new self($name, Type::String);
    }

    public static function numberField(string $name): self
    {
        return new self($name, Type::Number);
    }

    public static function integerField(string $name): self
    {
        return new self($name, Type::Integer);
    }

    public static function booleanField(string $name): self
    {
        return new self($name, Type::Boolean);
    }

    /**
     * @param class-string<BackedEnum> $backedEnumClass
     */
    public static function backedEnum(string $name, string $backedEnumClass): self
    {
        if (! is_subclass_of($backedEnumClass, BackedEnum::class)) {
            throw new InvalidArgumentException('The class must be a subclass of BackedEnum.');
        }

        $reflection = new ReflectionEnum($backedEnumClass);

        $type = match ($reflection->getProperty('value')->getType()?->getName()) {
            'int' => Type::Integer,
            'string' => Type::String,
            default => Type::String,
        };

        $self = new self($name, $type);
        $self->withPossibleValues(
            Vec\map(
                $backedEnumClass::cases(),
                static fn (BackedEnum $value): int|string => $value->value,
            ),
        );

        return $self;
    }

    public static function arrayField(string $name, IOField $element): self
    {
        $self = new self($name, Type::Array);
        $self->withChildren([$element]);

        return $self;
    }

    public static function objectField(string $name, IOField ...$children): self
    {
        $self = new self($name, Type::Object);
        if (count($children) > 0) {
            $self->withChildren(Vec\values($children));
        }

        return $self;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    /**
     * @param list<IOField> $children
     */
    public function withChildren(array $children): self
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return list<IOField>|null
     */
    public function children(): array|null
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

    public function asRequired(): self
    {
        $this->required = true;

        return $this;
    }

    public function asOptional(): self
    {
        $this->required = false;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function withPattern(string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function pattern(): string|null
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
    public function possibleValues(): array|null
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
