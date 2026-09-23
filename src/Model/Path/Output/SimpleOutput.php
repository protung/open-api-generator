<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use InvalidArgumentException;
use NoDiscard;
use Override;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Type;
use Psl;

use function array_is_list;
use function array_keys;
use function array_map;
use function array_values;
use function gettype;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function reset;

/**
 * @todo rename class, give it a better name
 */
class SimpleOutput implements Output
{
    /** @var non-empty-list<IOField> */
    private array $fields;

    /** @var array<string, mixed> */
    private array $example;

    /** @var non-empty-list<string>|null */
    private array|null $contentTypes = null;

    /**
     * @param non-empty-list<IOField> $fields
     * @param array<string, mixed>    $example
     */
    protected function __construct(array $fields, array $example)
    {
        $this->replaceFields($fields, $example);
    }

    /**
     * Lets an output which derives its shape from something else rebuild itself once that changes.
     *
     * @param non-empty-list<IOField> $fields
     * @param array<string, mixed>    $example
     */
    protected function replaceFields(array $fields, array $example): void
    {
        $this->fields  = $fields;
        $this->example = $example;
    }

    public static function fromIOFields(IOField $field, IOField ...$fields): self
    {
        $fields = [$field, ...Psl\Vec\values($fields)];

        return new self($fields, self::exampleFromFields($fields));
    }

    /**
     * @param non-empty-array<string, mixed> $data
     */
    public static function fromExampleData(array $data): self
    {
        return new self(
            self::createIOFields($data),
            $data,
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @return ($data is non-empty-array ? non-empty-list<IOField> : list<IOField>)
     */
    private static function createIOFields(array $data): array
    {
        return array_values(
            array_map(
                // Property names are strings, even where PHP turned a numeric one into an integer array key.
                static fn (int|string $fieldName, mixed $fieldValue): IOField => self::createIOField((string) $fieldName, $fieldValue),
                array_keys($data),
                $data,
            ),
        );
    }

    private static function createIOField(string $fieldName, mixed $fieldValue): IOField
    {
        if ($fieldValue === null) {
            return IOField::unknown($fieldName)->asNullable();
        }

        if (is_string($fieldValue)) {
            return IOField::stringField($fieldName);
        }

        if (is_int($fieldValue)) {
            return IOField::integerField($fieldName);
        }

        if (is_float($fieldValue)) {
            return IOField::numberField($fieldName);
        }

        if (is_bool($fieldValue)) {
            return IOField::booleanField($fieldName);
        }

        if (is_array($fieldValue)) {
            if (array_is_list($fieldValue)) {
                return IOField::arrayField($fieldName, self::createIOField($fieldName, reset($fieldValue)));
            }

            return IOField::objectField($fieldName, ...self::createIOFields($fieldValue));
        }

        throw new InvalidArgumentException(
            Psl\Str\format(
                'Only scalars or arrays can be used as example value for building SimpleOutput, "%s" given.',
                gettype($fieldValue),
            ),
        );
    }

    /**
     * @return non-empty-list<IOField>
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function example(): array
    {
        return $this->example;
    }

    /**
     * @param IOField[] $fields
     *
     * @return array<string, mixed>
     */
    private static function exampleFromFields(array $fields): array
    {
        $example = [];

        foreach ($fields as $field) {
            $example[$field->name()] = self::exampleFromField($field);
        }

        return $example;
    }

    /**
     * An example explicitly set on the field always wins over the one derived from its shape,
     * the same way IOFieldDescriber prefers it when describing the schema.
     */
    private static function exampleFromField(IOField $field): mixed
    {
        if ($field->example() !== null) {
            return $field->example();
        }

        $children = $field->children();
        if ($children !== null) {
            // An array field carries its element as its single child. The element has no name in JSON, so its example becomes the single entry of a list instead of a keyed value.
            if ($field->type() === Type::Array) {
                $element = Psl\Iter\first($children);

                return $element !== null ? [self::exampleFromField($element)] : [];
            }

            return self::exampleFromFields($children);
        }

        $exampleValue = Psl\Iter\first($field->possibleValues() ?? []);

        if ($exampleValue !== null) {
            return $exampleValue;
        }

        return $field->type()->example();
    }

    /**
     * Declares the content types the response is served with.
     *
     * Useful for error documents which Symfony renders in the format negotiated through the
     * "Accept" header, for example both "application/problem+json" and "application/json".
     */
    #[NoDiscard]
    public function withContentTypes(string $contentType, string ...$contentTypes): static
    {
        $clone = clone $this;
        $clone->replaceContentTypes($contentType, ...$contentTypes);

        return $clone;
    }

    /**
     * Lets an output set its content types while it is being constructed.
     */
    protected function replaceContentTypes(string $contentType, string ...$contentTypes): void
    {
        $this->contentTypes = [$contentType, ...Psl\Vec\values($contentTypes)];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function contentTypes(): array
    {
        return $this->contentTypes ?? [Output::CONTENT_TYPE_APPLICATION_JSON];
    }
}
