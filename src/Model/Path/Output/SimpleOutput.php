<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Model\Path\IOField;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Type;

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
        $this->fields  = $fields;
        $this->example = $example;
    }

    public static function fromIOFields(IOField ...$fields): self
    {
        return new self($fields, self::exampleFromFields($fields));
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
                $possibleValues = $field->possibleValues();
                if ($possibleValues !== null && $possibleValues !== []) {
                    $example[$field->name()] = reset($possibleValues);
                } else {
                    $example[$field->name()] = Type::example($field->type());
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
