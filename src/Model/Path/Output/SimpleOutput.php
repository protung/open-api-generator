<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Type;

/**
 * @todo rename class, give it a better name
 */
class SimpleOutput implements Output
{
    /** @var Field[] */
    private array $fields;

    public function __construct(Field ...$fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return Field[]
     */
    public function fields() : array
    {
        return $this->fields;
    }

    public function example() : array
    {
        $example = [];

        foreach ($this->fields as $field) {
            $example[$field->name()] = Type::example($field->type());
        }

        return $example;
    }
}
