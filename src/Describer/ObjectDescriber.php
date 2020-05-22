<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use Speicher210\OpenApiGenerator\Model\Definition;

interface ObjectDescriber
{
    /**
     * @return Reference|Schema
     */
    public function describe(Definition $definition) : SpecObjectInterface;
}
