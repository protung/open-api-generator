<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\SimpleOutput;

final class SimpleOutputDescriber implements OutputDescriber
{
    public function describe(Output $output): Schema
    {
        Assert::isInstanceOf($output, SimpleOutput::class);

        $properties = [];
        foreach ($output->fields() as $field) {
            $properties[$field->name()] = ['type' => $field->type()];

            if ($field->possibleValues() === null) {
                continue;
            }

            $properties[$field->name()]['enum'] = $field->possibleValues();
        }

        return new Schema(['type' => Type::OBJECT, 'properties' => $properties, 'example' => $output->example()]);
    }

    public function supports(Output $output): bool
    {
        return $output instanceof SimpleOutput;
    }
}
