<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\InputDescriber;

use cebe\openapi\spec\Operation;
use Protung\OpenApiGenerator\Model\Path\Input;

interface InputDescriber
{
    public function describe(Input $input, Operation $operation, string $httpMethod): void;

    public function supports(Input $input): bool;
}
