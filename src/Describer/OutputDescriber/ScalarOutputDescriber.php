<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Psl;
use Speicher210\OpenApiGenerator\Model\Path\Output;

final class ScalarOutputDescriber implements OutputDescriber
{
    public function describe(Output $output): Schema
    {
        $output = Psl\Type\instance_of(Output\ScalarOutput::class)->coerce($output);

        return new Schema(['type' => $output->type(), 'example' => $output->example()]);
    }

    public function supports(Output $output): bool
    {
        return $output instanceof Output\ScalarOutput;
    }
}
