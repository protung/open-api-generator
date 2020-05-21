<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Describer\Output;
use Speicher210\OpenApiGenerator\Describer\Query;
use Speicher210\OpenApiGenerator\Describer\RequestBodyContent;
use Speicher210\OpenApiGenerator\Model\FormDefinition;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Model\Path\Output\ErrorResponse;
use Speicher210\OpenApiGenerator\Processor\Path\Path;
use Speicher210\OpenApiGenerator\Processor\Path\SymfonyPathProcessor;
use Speicher210\OpenApiGenerator\Processor\Path\SymfonyRoutePath;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class Route
{
    private const PARAMETER_LOCATION_PATH = 'path';

    private Query $queryDescriber;

    private RequestBodyContent $requestBodyContentDescriber;

    private Output $outputDescriber;

    private RouteCollection $routeCollection;

    private FormFactory $formFactory;

    public function __construct(
        RouterInterface $router,
        Query $queryDescriber,
        RequestBodyContent $requestBodyContentDescriber,
        Output $outputDescriber,
        FormFactory $formFactory
    ) {
        $this->routeCollection = $router->getRouteCollection();

        $this->queryDescriber = $queryDescriber;
        $this->requestBodyContentDescriber = $requestBodyContentDescriber;
        $this->outputDescriber = $outputDescriber;
        $this->formFactory = $formFactory;
    }

    /**
     * @param Path[] $pathsConfig
     *
     * @return PathItem[]
     */
    public function processRoutes(array $pathsConfig): array
    {
        $symfonyPathProcessor = new SymfonyPathProcessor(
            $this->routeCollection,
            $this->queryDescriber,
            $this->requestBodyContentDescriber,
            $this->outputDescriber,
            $this->formFactory
        );

        $paths = [];
        foreach ($pathsConfig as $pathConfig) {
            if (!$pathConfig instanceof SymfonyRoutePath) {
                throw new \InvalidArgumentException('Unknown path config.');
            }

            $routePath = $pathConfig->routeName();
            $paths[$routePath] = $paths[$routePath] ?? new PathItem([]);


            foreach ($symfonyPathProcessor->process($pathConfig) as $pathOperation) {
                $path = $pathOperation->path();
                $paths[$path] = $paths[$path] ?? new PathItem([]);

                $paths[$path]->{$pathOperation->operationMethod()} = $pathOperation->operation();
            }
        }

        return $paths;
    }
}
