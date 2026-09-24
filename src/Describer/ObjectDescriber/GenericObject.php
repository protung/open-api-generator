<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\ObjectDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Override;
use Protung\OpenApiGenerator\Describer\ObjectDescriber;
use Protung\OpenApiGenerator\Model\Definition;

/**
 * Describes any object as an object, without its properties.
 * The last resort for a class no other describer knows.
 */
final class GenericObject implements Describer
{
    #[Override]
    public function describeInSchema(Schema $schema, Definition $definition, ObjectDescriber $objectDescriber): void
    {
        $schema->type = Type::OBJECT;
    }

    #[Override]
    public function supports(Definition $definition): bool
    {
        return true;
    }
}
