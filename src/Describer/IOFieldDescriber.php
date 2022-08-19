<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Type as ModelType;
use Psl;

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
                $children               = Psl\Type\non_empty_vec(Psl\Type\instance_of(IOField::class))->coerce($children);
                $properties[$fieldName] = new Schema(['type' => Type::ARRAY, 'items' => $this->describeField($children[0])]);
                if ($field->isNullable()) {
                    $properties[$fieldName]->nullable = true;
                }
            } elseif ($field->type() === ModelType::OBJECT) {
                if ($children !== null) {
                    $properties[$fieldName]           = $this->describeFields($children);
                    $properties[$fieldName]->required = $this->extractRequiredFields(...$children);
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

        $required = $this->extractRequiredFields(...$fields);

        return new Schema(['type' => Type::OBJECT, 'properties' => $properties, 'required' => $required]);
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

    /**
     * @return list<string>
     */
    private function extractRequiredFields(IOField ...$fields): array
    {
        return Psl\Vec\map(
            Psl\Vec\filter(
                $fields,
                static fn (IOField $child): bool => $child->isRequired()
            ),
            static fn (IOField $child): string => $child->name()
        );
    }
}
