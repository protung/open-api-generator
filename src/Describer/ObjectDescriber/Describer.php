<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\ObjectDescriber;

use cebe\openapi\spec\Schema;
use Speicher210\OpenApiGenerator\Describer\ObjectDescriber;
use Speicher210\OpenApiGenerator\Model\Definition;

interface Describer
{
    public function describeInSchema(Schema $schema, Definition $definition, ObjectDescriber $objectDescriber): void;

    public function supports(Definition $definition): bool;
}
