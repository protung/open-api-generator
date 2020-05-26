<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Input;

use Speicher210\OpenApiGenerator\Model\Path\IOField;
use Speicher210\OpenApiGenerator\Model\Type;

/**
 * @todo rename class, give it a better name
 */
class SimpleInput extends BaseInput
{
    /** @var IOField[] */
    private array $fields;

    public function __construct(string $location, IOField ...$fields)
    {
        $this->fields = $fields;

        $this->setLocation($location);
    }

    /**
     * @return IOField[]
     */
    public function fields() : array
    {
        return $this->fields;
    }

    /**
     * @return array<string,mixed>
     */
    public function example() : array
    {
        $example = [];

        foreach ($this->fields as $field) {
            $example[$field->name()] = Type::example($field->type());
        }

        return $example;
    }
}
