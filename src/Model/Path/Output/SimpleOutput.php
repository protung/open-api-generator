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

    public function __construct(IOField ...$fields)
    {
        $this->fields = $fields;
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
        $example = [];

        foreach ($this->fields as $field) {
            $possibleValues = $field->possibleValues();
            if ($possibleValues !== null && $possibleValues !== []) {
                $example[$field->name()] = reset($possibleValues);
            } else {
                $example[$field->name()] = Type::example($field->type());
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
