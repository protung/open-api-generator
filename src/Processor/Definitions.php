<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use cebe\openapi\spec\Components;
use cebe\openapi\spec\OpenApi;
use Speicher210\OpenApiGenerator\Model\ModelRegistry;
use Speicher210\OpenApiGenerator\Model\Specification;
use Speicher210\OpenApiGenerator\Resolver\DefinitionName;
use function count;
use function ksort;

final class Definitions implements Processor
{
    private ModelRegistry $modelRegistry;

    private DefinitionName $definitionNameResolver;

    public function __construct(ModelRegistry $modelRegistry, DefinitionName $definitionNameResolver)
    {
        $this->modelRegistry          = $modelRegistry;
        $this->definitionNameResolver = $definitionNameResolver;
    }

    public function process(OpenApi $openApi, Specification $specification) : void
    {
        $definitions = [];
        foreach ($this->modelRegistry->referencedModels() as $model) {
            $definitions[$this->definitionNameResolver->getName($model->definition())] = $model->schema();
        }

        if ($openApi->components === null) {
            $openApi->components = new Components([]);
        }

        if (count($definitions) <= 0) {
            return;
        }

        ksort($definitions);
        $openApi->components->schemas = $definitions;
    }
}
