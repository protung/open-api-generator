<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Processor;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
use Override;
use Protung\OpenApiGenerator\Model\Specification;
use Protung\OpenApiGenerator\Processor\Path\PathProcessor;

use function ksort;

final class PathsProcessor implements Processor
{
    private PathProcessor $pathProcessor;

    public function __construct(PathProcessor $pathProcessor)
    {
        $this->pathProcessor = $pathProcessor;
    }

    #[Override]
    public function process(OpenApi $openApi, Specification $specification): void
    {
        $openApiPaths = [];
        foreach ($specification->paths() as $pathDefinition) {
            $alwaysAddedInputs = $specification->alwaysAddedInputs();
            if ($alwaysAddedInputs !== []) {
                $pathDefinition = $pathDefinition->withAddedInputs(...$alwaysAddedInputs);
            }

            $alwaysAddedResponses = $specification->alwaysAddedResponses();
            if ($alwaysAddedResponses !== []) {
                $pathDefinition = $pathDefinition->withAddedResponses(...$alwaysAddedResponses);
            }

            foreach ($this->pathProcessor->process($pathDefinition) as $pathOperation) {
                $path                  = $pathOperation->path();
                $openApiPaths[$path] ??= new PathItem([]);

                // Set through cebe's magic setter, which keeps the operations in the order the routes declare them.
                $openApiPaths[$path]->__set($pathOperation->operationMethod(), $pathOperation->operation());
            }
        }

        ksort($openApiPaths);

        $openApi->paths = new Paths($openApiPaths);
    }
}
