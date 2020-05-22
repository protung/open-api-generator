<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use cebe\openapi\spec\PathItem;
use Speicher210\OpenApiGenerator\Processor\Path\Path;
use Speicher210\OpenApiGenerator\Processor\Path\PathProcessor;

final class PathsProcessor
{
    private PathProcessor $pathProcessor;

    public function __construct(PathProcessor $pathProcessor)
    {
        $this->pathProcessor = $pathProcessor;
    }

    /**
     * @return array<string,PathItem>
     */
    public function process(Path ...$paths) : array
    {
        $openApiPaths = [];
        foreach ($paths as $pathDefinition) {
            foreach ($this->pathProcessor->process($pathDefinition) as $pathOperation) {
                $path                = $pathOperation->path();
                $openApiPaths[$path] = $openApiPaths[$path] ?? new PathItem([]);

                $openApiPaths[$path]->{$pathOperation->operationMethod()} = $pathOperation->operation();
            }
        }

        return $openApiPaths;
    }
}
