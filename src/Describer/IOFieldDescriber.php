<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Model\Path\IOField;

final class IOFieldDescriber
{
    /**
     * @param IOField[] $fields
     */
    public function describeFields(array $fields): Schema
    {
        $properties = [];
        foreach ($fields as $field) {
            $fieldName = $field->name();
            if ($field->children() !== null) {
                $properties[$fieldName] = $this->describeFields($field->children());
            } else {
                $properties[$fieldName] = ['type' => $field->type()];

                if ($field->possibleValues() === null) {
                    continue;
                }

                $properties[$fieldName]['enum'] = $field->possibleValues();
            }
        }

        return new Schema(['type' => Type::OBJECT, 'properties' => $properties]);
    }
}
