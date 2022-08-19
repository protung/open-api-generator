<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator;

use cebe\openapi\spec\OpenApi;
use Protung\OpenApiGenerator\Model\Specification;
use Protung\OpenApiGenerator\Processor\Definitions;
use Protung\OpenApiGenerator\Processor\InfoProcessor;
use Protung\OpenApiGenerator\Processor\PathsProcessor;
use Protung\OpenApiGenerator\Processor\SecurityDefinitions;

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
