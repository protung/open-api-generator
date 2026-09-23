<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path;

use BackedEnum;
use InvalidArgumentException;
use NoDiscard;
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

        $type = match ($reflection->getBackingType()?->getName()) {
            'int' => Type::Integer,
            'string' => Type::String,
            default => Type::String,
        };

        return (new self($name, $type))->withPossibleValues(
            Vec\map(
                $backedEnumClass::cases(),
                static fn (BackedEnum $value): int|string => $value->value,
            ),
        );
    }

    public static function arrayField(string $name, IOField $element): self
    {
        return (new self($name, Type::Array))->withChildren([$element]);
    }

    public static function objectField(string $name, IOField ...$children): self
    {
        $self = new self($name, Type::Object);
        if (count($children) > 0) {
            return $self->withChildren(Vec\values($children));
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
    #[NoDiscard]
    public function withChildren(array $children): self
    {
        $clone           = clone $this;
        $clone->children = $children;

        return $clone;
    }

    /**
     * @return list<IOField>|null
     */
    public function children(): array|null
    {
        return $this->children;
    }

    #[NoDiscard]
    public function asNullable(): self
    {
        $clone           = clone $this;
        $clone->nullable = true;

        return $clone;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    #[NoDiscard]
    public function asRequired(): self
    {
        $clone           = clone $this;
        $clone->required = true;

        return $clone;
    }

    #[NoDiscard]
    public function asOptional(): self
    {
        $clone           = clone $this;
        $clone->required = false;

        return $clone;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    #[NoDiscard]
    public function withPattern(string $pattern): self
    {
        $clone          = clone $this;
        $clone->pattern = $pattern;

        return $clone;
    }

    public function pattern(): string|null
    {
        return $this->pattern;
    }

    /**
     * @param mixed[] $possibleValues
     */
    #[NoDiscard]
    public function withPossibleValues(array $possibleValues): self
    {
        $clone                 = clone $this;
        $clone->possibleValues = $possibleValues;

        return $clone;
    }

    /**
     * @return mixed[]|null
     */
    public function possibleValues(): array|null
    {
        return $this->possibleValues;
    }

    #[NoDiscard]
    public function withExample(mixed $example): self
    {
        $clone          = clone $this;
        $clone->example = $example;

        return $clone;
    }

    public function example(): mixed
    {
        return $this->example;
    }
}
