<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Override;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Type;
use Psl;

final class FileOutputDescriber implements OutputDescriber
{
    #[Override]
    public function describe(Output $output): Schema
    {
        Psl\Type\instance_of(Output\FileOutput::class)->coerce($output);

        return new Schema(['type' => Type::String, 'format' => 'binary']);
    }

    #[Override]
    public function supports(Output $output): bool
    {
        return $output instanceof Output\FileOutput;
    }
}
