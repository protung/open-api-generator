<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
use Speicher210\OpenApiGenerator\Model\Path\Path;
use Speicher210\OpenApiGenerator\Model\Specification;
use Speicher210\OpenApiGenerator\Processor\Path\PathProcessor;

use function ksort;

final class PathsProcessor implements Processor
{
    private PathProcessor $pathProcessor;

    public function __construct(PathProcessor $pathProcessor)
    {
        $this->pathProcessor = $pathProcessor;
    }

    public function process(OpenApi $openApi, Specification $specification): void
    {
        $openApiPaths = [];
        foreach ($specification->paths() as $pathDefinition) {
            $this->addAlwaysAdded($pathDefinition, $specification);

            foreach ($this->pathProcessor->process($pathDefinition) as $pathOperation) {
                $path                  = $pathOperation->path();
                $openApiPaths[$path] ??= new PathItem([]);

                $openApiPaths[$path]->{$pathOperation->operationMethod()} = $pathOperation->operation();
            }
        }

        ksort($openApiPaths);

        $openApi->paths = new Paths($openApiPaths);
    }

    private function addAlwaysAdded(Path $pathDefinition, Specification $specification): void
    {
        foreach ($specification->alwaysAddedInputs() as $alwaysAddedInput) {
            $pathDefinition->addInput($alwaysAddedInput);
        }

        foreach ($specification->alwaysAddedResponses() as $alwaysAddedResponse) {
            $pathDefinition->addResponse($alwaysAddedResponse);
        }
    }
}
