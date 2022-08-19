<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Model\Path\Output;

interface OutputDescriber
{
    public function describe(Output $output): Schema;

    public function supports(Output $output): bool;
}
