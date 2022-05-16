<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Psl;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Type;

final class FileOutputDescriber implements OutputDescriber
{
    public function describe(Output $output): Schema
    {
        Psl\Type\instance_of(Output\FileOutput::class)->coerce($output);

        return new Schema(['type' => Type::STRING, 'format' => 'binary']);
    }

    public function supports(Output $output): bool
    {
        return $output instanceof Output\FileOutput;
    }
}
