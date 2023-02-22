<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use InvalidArgumentException;
use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Path\Output;
use Psl;

use function array_is_list;
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
    /** @var IOField[] */
    private array $fields;

    /** @var array<mixed> */
    private array $example;

    /**
     * @param IOField[]    $fields
     * @param array<mixed> $example
     */
    protected function __construct(array $fields, array $example)
    {
        Assert::minCount($fields, 1, 'At least one field should be defined.');

        $this->fields  = $fields;
        $this->example = $example;
    }

    public static function fromIOFields(IOField ...$fields): self
    {
        return new self($fields, self::exampleFromFields($fields));
    }

    /**
     * @param array<mixed> $data
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
     * @return array<IOField>
     */
    private static function createIOFields(array $data): array
    {
        $fields = [];
        foreach ($data as $fieldName => $fieldValue) {
            if ($fieldValue === null) {
                $fields[] = IOField::unknown($fieldName)->asNullable();
            } elseif (is_string($fieldValue)) {
                $fields[] = IOField::stringField($fieldName);
            } elseif (is_int($fieldValue)) {
                $fields[] = IOField::integerField($fieldName);
            } elseif (is_float($fieldValue)) {
                $fields[] = IOField::numberField($fieldName);
            } elseif (is_bool($fieldValue)) {
                $fields[] = IOField::booleanField($fieldName);
            } elseif (is_array($fieldValue)) {
                if (array_is_list($fieldValue)) {
                    $fields[] = IOField::arrayField(
                        $fieldName,
                        self::createIOFields([$fieldName => reset($fieldValue)])[0],
                    );
                } else {
                    $fields[] = IOField::objectField(
                        $fieldName,
                        ...self::createIOFields($fieldValue),
                    );
                }
            } else {
                throw new InvalidArgumentException(
                    Psl\Str\format(
                        'Only scalars or arrays can be used as example value for building SimpleOutput, "%s" given.',
                        gettype($fieldValue),
                    ),
                );
            }
        }

        return $fields;
    }

    /**
     * @return IOField[]
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string,mixed>
     */
    public function example(): array
    {
        return $this->example;
    }

    /**
     * @param IOField[] $fields
     *
     * @return mixed[]
     */
    private static function exampleFromFields(array $fields): array
    {
        $example = [];

        foreach ($fields as $field) {
            if ($field->children() !== null) {
                $example[$field->name()] = self::exampleFromFields($field->children());
            } else {
                $exampleValue = Psl\Iter\first($field->possibleValues() ?? []);

                if ($exampleValue !== null) {
                    $example[$field->name()] = $exampleValue;
                } else {
                    $example[$field->name()] = $field->type()->example();
                }
            }
        }

        return $example;
    }

    /**
     * {@inheritDoc}
     */
    public function contentTypes(): array
    {
        return [Output::CONTENT_TYPE_APPLICATION_JSON];
    }
}
