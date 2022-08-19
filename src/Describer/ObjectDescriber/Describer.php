<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\ObjectDescriber;

use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Describer\ObjectDescriber;
use Protung\OpenApiGenerator\Model\Definition;

interface Describer
{
    public function describeInSchema(Schema $schema, Definition $definition, ObjectDescriber $objectDescriber): void;

    public function supports(Definition $definition): bool;
}
