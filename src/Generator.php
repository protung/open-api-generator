<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator;

use Speicher210\OpenApiGenerator\Processor\Definitions;
use Speicher210\OpenApiGenerator\Processor\Info;
use Speicher210\OpenApiGenerator\Processor\Route;
use Speicher210\OpenApiGenerator\Processor\SecurityDefinitions;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Paths;

final class Generator
{
    private const OPEN_API_VERSION = '3.0.2';

    private Info $infoProcessor;

    private SecurityDefinitions $securityDefinitionsProcessor;

    private Route $routeProcessor;

    private Definitions $definitionsProcessor;

    public function __construct(
        Info $infoProcessor,
        SecurityDefinitions $securityDefinitionsProcessor,
        Route $routeProcessor,
        Definitions $definitionsProcessor
    ) {
        $this->infoProcessor = $infoProcessor;
        $this->securityDefinitionsProcessor = $securityDefinitionsProcessor;
        $this->routeProcessor = $routeProcessor;
        $this->definitionsProcessor = $definitionsProcessor;
    }

    /**
     * @param mixed[] $config
     */
    public function generate(array $config): OpenApi
    {
        $openApi = new OpenApi(
            [
                'openapi' => self::OPEN_API_VERSION,
                'info' => $this->infoProcessor->process($config['info']),
            ]
        );

        $paths = $this->routeProcessor->processRoutes($config['paths']);
        \ksort($paths);
        $openApi->paths = new Paths($paths);
//
//        $definitions = $this->definitionsProcessor->process();
//        \ksort($definitions);

        $openApi->components = new Components(
            [
                'securitySchemes' => $this->securityDefinitionsProcessor->process($config['securityDefinitions']),
//                'schemas' => $definitions,
            ]
        );

        return $openApi;
    }
}
