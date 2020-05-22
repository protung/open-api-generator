<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use cebe\openapi\spec\Info as InfoSpec;
use Speicher210\OpenApiGenerator\Model\Info\Info;

final class InfoProcessor
{
    private string $apiVersion;

    public function __construct(string $apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    public function process(Info $info) : InfoSpec
    {
        return new InfoSpec(
            [
                'title' => $info->title(),
                'description' => $info->description(),
                'version' => $info->apiVersion() ?? $this->apiVersion,
            ]
        );
    }
}
