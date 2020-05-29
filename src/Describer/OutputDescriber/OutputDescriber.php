<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Speicher210\OpenApiGenerator\Model\Path\Output;

interface OutputDescriber
{
    public function describe(Output $output) : Schema;

    public function supports(Output $output) : bool;
}
