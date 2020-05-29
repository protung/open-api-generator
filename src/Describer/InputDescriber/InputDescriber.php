<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\InputDescriber;

use cebe\openapi\spec\Operation;
use Speicher210\OpenApiGenerator\Model\Path\Input;

interface InputDescriber
{
    public function describe(Input $input, Operation $operation, string $httpMethod) : void;

    public function supports(Input $input) : bool;
}
