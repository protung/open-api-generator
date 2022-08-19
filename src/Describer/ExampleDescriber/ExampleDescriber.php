<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\ExampleDescriber;

use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Model\Path\Output;

interface ExampleDescriber
{
    public function describe(Schema $schema, Output $output): void;

    public function supports(Output $output): bool;
}
