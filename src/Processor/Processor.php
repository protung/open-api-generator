<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Processor;

use cebe\openapi\spec\OpenApi;
use Protung\OpenApiGenerator\Model\Specification;

interface Processor
{
    public function process(OpenApi $openApi, Specification $specification): void;
}
