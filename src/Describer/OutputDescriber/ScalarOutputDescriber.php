<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Model\Path\Output;
use Psl;

final class ScalarOutputDescriber implements OutputDescriber
{
    public function describe(Output $output): Schema
    {
        $output = Psl\Type\instance_of(Output\ScalarOutput::class)->coerce($output);

        return new Schema(['type' => $output->type()->value, 'example' => $output->example()]);
    }

    public function supports(Output $output): bool
    {
        return $output instanceof Output\ScalarOutput;
    }
}
