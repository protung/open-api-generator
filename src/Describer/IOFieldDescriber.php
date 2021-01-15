<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\IOField;
use Speicher210\OpenApiGenerator\Model\Type as ModelType;

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
            $children  = $field->children();
            $fieldName = $field->name();

            if ($field->type() === ModelType::ARRAY) {
                Assert::notNull($children);
                Assert::count($children, 1);
                $properties[$fieldName] = new Schema(['type' => Type::ARRAY, 'items' => $this->describeField($children[0])]);
                if ($field->isNullable()) {
                    $properties[$fieldName]->nullable = true;
                }
            } elseif ($field->type() === ModelType::OBJECT) {
                if ($children !== null) {
                    $properties[$fieldName] = $this->describeFields($children);
                } else {
                    $properties[$fieldName] = new Schema(['type' => Type::OBJECT]);
                }

                if ($field->isNullable()) {
                    $properties[$fieldName]->nullable = true;
                }
            } else {
                $properties[$fieldName] = $this->describeField($field);
            }
        }

        return new Schema(['type' => Type::OBJECT, 'properties' => $properties]);
    }

    private function describeField(IOField $field): Schema
    {
        if ($field->type() === ModelType::OBJECT) {
            $schema = $this->describeFields($field->children() ?? []);

            if ($field->isNullable()) {
                $schema->nullable = true;
            }

            return $schema;
        }

        $schema = [];
        if ($field->type() !== ModelType::UNKNOWN) {
            $schema['type'] = $field->type();
        }

        if ($field->isNullable()) {
            $schema['nullable'] = true;
        }

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
