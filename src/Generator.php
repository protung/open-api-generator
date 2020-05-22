<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator;

use cebe\openapi\spec\Components;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Paths;
use Speicher210\OpenApiGenerator\Processor\Definitions;
use Speicher210\OpenApiGenerator\Processor\InfoProcessor;
use Speicher210\OpenApiGenerator\Processor\PathsProcessor;
use Speicher210\OpenApiGenerator\Processor\SecurityDefinitions;
use function ksort;

final class Generator
{
    private const OPEN_API_VERSION = '3.0.2';

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

    /**
     * @param mixed[] $config
     */
    public function generate(array $config) : OpenApi
    {
        $openApi = new OpenApi(
            [
                'openapi' => self::OPEN_API_VERSION,
                'info' => $this->infoProcessor->process($config['info']),
            ]
        );

        $paths = $this->pathsProcessor->process(...$config['paths']);
        ksort($paths);
        $openApi->paths = new Paths($paths);

        $definitions = $this->definitionsProcessor->process();
        ksort($definitions);

        $openApi->components = new Components(
            [
                'securitySchemes' => $this->securityDefinitionsProcessor->process($config['securityDefinitions']),
            //                'schemas' => $definitions,
            ]
        );

        return $openApi;
    }
}
