<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\ExampleDescriber;

use cebe\openapi\spec\Schema;
use Speicher210\OpenApiGenerator\Model\Path\Output;

interface ExampleDescriber
{
    public function describe(Schema $schema, Output $output) : void;

    public function supports(Output $output) : bool;
}
