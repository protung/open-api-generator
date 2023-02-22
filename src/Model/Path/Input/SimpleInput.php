<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Input;

use Protung\OpenApiGenerator\Model\Path\InputLocation;
use Protung\OpenApiGenerator\Model\Path\IOField;

/**
 * @todo rename class, give it a better name
 */
class SimpleInput extends BaseInput
{
    /** @var IOField[] */
    private array $fields;

    public function __construct(InputLocation $location, IOField ...$fields)
    {
        $this->fields = $fields;

        $this->setLocation($location);
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
            $example[$field->name()] = $field->type()->example();
        }

        return $example;
    }
}
