<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use Speicher210\OpenApiGenerator\Describer\ObjectDescriber\Describer;
use Speicher210\OpenApiGenerator\Model\Definition;
use Speicher210\OpenApiGenerator\Model\ModelRegistry;

final class ObjectDescriber
{
    private ModelRegistry $modelRegistry;

    private Describer $describer;

    public function __construct(ModelRegistry $modelRegistry, Describer $describer)
    {
        $this->modelRegistry = $modelRegistry;
        $this->describer     = $describer;
    }

    /**
     * @return Reference|Schema
     */
    public function describe(Definition $definition) : SpecObjectInterface
    {
        if (! $this->modelRegistry->schemaExistsForDefinition($definition)) {
            $this->modelRegistry->addSchema(
                $definition,
                $this->createSchema($definition)
            );
        }

        return $this->modelRegistry->getSchema($definition);
    }

    private function createSchema(Definition $definition) : Schema
    {
        $schema = new Schema([]);

        $this->describer->describeInSchema($schema, $definition, $this);

        return $schema;
    }
}
