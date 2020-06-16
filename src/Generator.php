<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator;

use cebe\openapi\spec\OpenApi;
use Speicher210\OpenApiGenerator\Model\Specification;
use Speicher210\OpenApiGenerator\Processor\Definitions;
use Speicher210\OpenApiGenerator\Processor\InfoProcessor;
use Speicher210\OpenApiGenerator\Processor\PathsProcessor;
use Speicher210\OpenApiGenerator\Processor\SecurityDefinitions;

final class Generator
{
    private const OPEN_API_VERSION = '3.0.3';

    private InfoProcessor $infoProcessor;

    private SecurityDefinitions $securityDefinitionsProcessor;

    private PathsProcessor $pathsProcessor;

    private Definitions $definitionsProcessor;

    public function __construct(
        InfoProcessor $infoProcessor,
        SecurityDefinitions $securityDefinitionsProcessor,
        PathsProcessor $pathsProcessor,
        Definitions $definitionsProcessor
    ) {
        $this->infoProcessor                = $infoProcessor;
        $this->securityDefinitionsProcessor = $securityDefinitionsProcessor;
        $this->pathsProcessor               = $pathsProcessor;
        $this->definitionsProcessor         = $definitionsProcessor;
    }

    public function generate(Specification $specification): OpenApi
    {
        $openApi = new OpenApi(
            [
                'openapi' => self::OPEN_API_VERSION,
            ]
        );

        $this->infoProcessor->process($openApi, $specification);
        $this->pathsProcessor->process($openApi, $specification);
        $this->securityDefinitionsProcessor->process($openApi, $specification);
        $this->definitionsProcessor->process($openApi, $specification);

        return $openApi;
    }
}
