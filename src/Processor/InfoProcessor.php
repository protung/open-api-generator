<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Processor;

use cebe\openapi\spec\Info as InfoSpec;
use cebe\openapi\spec\OpenApi;
use Override;
use Protung\OpenApiGenerator\Model\Specification;

final class InfoProcessor implements Processor
{
    private string $apiVersion;

    public function __construct(string $apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    #[Override]
    public function process(OpenApi $openApi, Specification $specification): void
    {
        $openApi->info = new InfoSpec(
            [
                'title' => $specification->info()->title(),
                'description' => $specification->info()->description(),
                'version' => $specification->info()->apiVersion() ?? $this->apiVersion,
            ],
        );
    }
}
