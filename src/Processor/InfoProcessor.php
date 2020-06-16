<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use cebe\openapi\spec\Info as InfoSpec;
use cebe\openapi\spec\OpenApi;
use Speicher210\OpenApiGenerator\Model\Specification;

final class InfoProcessor implements Processor
{
    private string $apiVersion;

    public function __construct(string $apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    public function process(OpenApi $openApi, Specification $specification): void
    {
        $openApi->info = new InfoSpec(
            [
                'title' => $specification->info()->title(),
                'description' => $specification->info()->description(),
                'version' => $specification->info()->apiVersion() ?? $this->apiVersion,
            ]
        );
    }
}
