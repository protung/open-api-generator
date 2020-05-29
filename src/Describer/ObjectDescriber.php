<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
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

    public function describe(Definition $definition) : Schema
    {
        if (! $this->modelRegistry->schemaExistsForDefinition($definition)) {
            $this->modelRegistry->addSchema(
                $definition,
                $this->createSchema($definition),
                false
            );
        }

        return $this->modelRegistry->getSchema($definition);
    }

    public function describeAsReference(Definition $definition) : Reference
    {
        if (! $this->modelRegistry->schemaExistsForDefinition($definition)) {
            $this->modelRegistry->addSchema(
                $definition,
                $this->createSchema($definition),
                true
            );
        }

        return $this->modelRegistry->getReference($definition);
    }

    private function createSchema(Definition $definition) : Schema
    {
        $schema = new Schema([]);

        $this->describer->describeInSchema($schema, $definition, $this);

        return $schema;
    }
}
