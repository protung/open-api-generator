<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\IOField;

/**
 * @todo support array of arrays
 */
final class IOFieldDescriber
{
    /**
     * @param IOField[] $fields
     */
    public function describeFields(array $fields): Schema
    {
        $properties = [];
        foreach ($fields as $field) {
            $children = $field->children();

            $fieldName = $field->name();

            $properties[$fieldName] = ['type' => $field->type()];
            if ($field->type() === \Speicher210\OpenApiGenerator\Model\Type::ARRAY) {
                if ($children !== null) {
                    Assert::count($children, 1);
                    $properties[$fieldName]['items'] = $this->describeField($children[0]);
                }
            } elseif ($field->type() === \Speicher210\OpenApiGenerator\Model\Type::OBJECT) {
                if ($children !== null) {
                    $properties[$fieldName] = $this->describeFields($children);
                }
            } else {
                $properties[$fieldName] = $this->describeField($field);
            }
        }

        return new Schema(['type' => Type::OBJECT, 'properties' => $properties]);
    }

    private function describeField(IOField $field): Schema
    {
        if ($field->type() === \Speicher210\OpenApiGenerator\Model\Type::OBJECT) {
            return $this->describeFields($field->children() ?? []);
        }

        $schema = ['type' => $field->type()];

        if ($field->possibleValues() !== null) {
            $schema['enum'] = $field->possibleValues();
        }

        if ($field->pattern() !== null) {
            $schema['pattern'] = $field->pattern();
        }

        if ($field->example() !== null) {
            $schema['example'] = $field->example();
        }

        return new Schema($schema);
    }
}
