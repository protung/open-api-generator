<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path;

use BackedEnum;
use InvalidArgumentException;
use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Model\Type;
use Psl\Vec;
use ReflectionEnum;

use function count;
use function is_subclass_of;

final class IOField
{
    private string $name;

    private string $type;

    private string|null $pattern = null;

    /** @var mixed[]|null */
    private array|null $possibleValues = null;

    /** @var list<IOField>|null */
    private array|null $children = null;

    private bool $nullable = false;

    private mixed $example = null;

    private bool $required = true;

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
            'int' => Type::INTEGER,
            'string' => Type::STRING,
            default => Type::STRING,
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
