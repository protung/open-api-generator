<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\InputDescriber;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Schema;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use function array_merge;

final class SimpleInputDescriber implements InputDescriber
{
    public function describe(Input $input, Operation $operation, string $httpMethod) : void
    {
        Assert::isInstanceOf($input, Input\SimpleInput::class);

        $parameters = [];
        foreach ($input->fields() as $field) {
            $parameter           = new Parameter(['name' => $field->name(), 'in' => $input->location()]);
            $parameter->required = $input->isInPath();

            $parameterSchema = new Schema(
                [
                    'type' => $field->type(),
                ]
            );
            $possibleValues  = $field->possibleValues();
            $fieldPattern    = $field->pattern();
            if ($possibleValues !== null) {
                $parameterSchema->enum = $possibleValues;
            } elseif ($fieldPattern !== null) {
                $parameterSchema->pattern = $fieldPattern;
            }

            $parameter->schema = $parameterSchema;
            $parameters[]      = $parameter;
        }

        $operation->parameters = array_merge($operation->parameters, $parameters);
    }

    public function supports(Input $input) : bool
    {
        return $input instanceof Input\SimpleInput;
    }
}
