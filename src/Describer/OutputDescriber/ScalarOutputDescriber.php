<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\Output;

final class ScalarOutputDescriber implements OutputDescriber
{
    public function describe(Output $output) : Schema
    {
        Assert::isInstanceOf($output, Output\ScalarOutput::class);

        return new Schema(['type' => $output->type(), 'example' => $output->example()]);
    }

    public function supports(Output $output) : bool
    {
        return $output instanceof Output\ScalarOutput;
    }
}
