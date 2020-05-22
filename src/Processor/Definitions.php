<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use cebe\openapi\spec\Schema;
use Speicher210\OpenApiGenerator\Model\ModelRegistry;
use Speicher210\OpenApiGenerator\Resolver\DefinitionName;

final class Definitions
{
    private ModelRegistry $modelRegistry;

    private DefinitionName $definitionNameResolver;

    public function __construct(ModelRegistry $modelRegistry, DefinitionName $definitionNameResolver)
    {
        $this->modelRegistry          = $modelRegistry;
        $this->definitionNameResolver = $definitionNameResolver;
    }

    /**
     * @return array<string,Schema>
     */
    public function process() : array
    {
        $definitions = [];
        foreach ($this->modelRegistry->models() as $model) {
            $definitions[$this->definitionNameResolver->getName($model->definition())] = $model->schema();
        }

        return $definitions;
    }
}
