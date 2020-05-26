<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use cebe\openapi\spec\OpenApi;
use Speicher210\OpenApiGenerator\Model\Specification;

interface Processor
{
    public function process(OpenApi $openApi, Specification $specification) : void;
}
