<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Model\Path\Output;

interface OutputDescriber
{
    /**
     * @param \Protung\OpenApiGenerator\Describer\OutputDescriber $outputDescriber Describes outputs nested in this one.
     */
    public function describe(Output $output, \Protung\OpenApiGenerator\Describer\OutputDescriber $outputDescriber): Schema;

    public function supports(Output $output): bool;
}
