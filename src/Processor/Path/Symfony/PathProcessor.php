<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor\Path\Symfony;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Type;
use InvalidArgumentException;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Describer\InputDescriber;
use Speicher210\OpenApiGenerator\Describer\OutputDescriber;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use Speicher210\OpenApiGenerator\Model\Path\IOField;
use Speicher210\OpenApiGenerator\Model\Path\Path;
use Speicher210\OpenApiGenerator\Processor\Path\PathOperation;
use Speicher210\OpenApiGenerator\Processor\Path\PathProcessor as PathProcessorInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

use function array_filter;
use function explode;
use function sprintf;
use function strpos;

use const ARRAY_FILTER_USE_BOTH;

final class PathProcessor implements PathProcessorInterface
{
    private RouteCollection $routeCollection;

    private InputDescriber $inputDescriber;

    private OutputDescriber $outputDescriber;

    public function __construct(
        RouteCollection $routeCollection,
        InputDescriber $inputDescriber,
        OutputDescriber $outputDescriber
    ) {
        $this->routeCollection = $routeCollection;
        $this->inputDescriber  = $inputDescriber;
        $this->outputDescriber = $outputDescriber;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Path $path): array
    {
        Assert::isInstanceOf($path, SymfonyRoutePath::class);

        $symfonyRoute = $this->routeCollection->get($path->routeName());

        if ($symfonyRoute === null) {
            throw new InvalidArgumentException(
                sprintf('Defined "%s" route in API doc configuration does not exist.', $path->routeName())
            );
        }

        return $this->processRoute($symfonyRoute, $path);
    }

    /**
     * @return PathOperation[]
     */
    private function processRoute(SymfonyRoute $route, SymfonyRoutePath $path): array
    {
        $operations = [];
        foreach ($route->getMethods() as $method) {
            $operation = new Operation(
                array_filter(
                    [
                        'summary' => $path->summary(),
                        'description' => $path->description(),
                        'tags' => [$path->tag()],
                        'deprecated' => $path->isDeprecated(),
                        'security' => $path->security()->references(),
                        'parameters' => [],
                        'responses' => new Responses([]),
                    ],
                    /**
                     * @param mixed $value
                     */
                    static function ($value, string $key): bool {
                        if ($key === 'deprecated' && $value === false) {
                            return false;
                        }

                        return $value !== null;
                    },
                    ARRAY_FILTER_USE_BOTH
                )
            );

            $this->processInputs(
                $operation,
                $method,
                $this->extractInputFromRoute($route),
                ...$path->input()
            );
            $this->processResponses(
                $operation,
                ...$path->responses(),
            );

            $operations[] = new PathOperation(
                $method,
                $route->getPath(),
                $operation
            );
        }

        return $operations;
    }

    private function extractInputFromRoute(SymfonyRoute $route): Input\PathInput
    {
        $ioFields = [];
        foreach ($route->compile()->getPathVariables() as $pathVariable) {
            $requirement = $route->getRequirement($pathVariable);

            $pattern = $possibleValues = null;

            if ($requirement !== null && strpos($requirement, '|') !== false) {
                $possibleValues = explode('|', $requirement);
            } else {
                $pattern = $requirement;
            }

            $ioFields[] = new IOField($pathVariable, Type::STRING, $pattern, $possibleValues);
        }

        return Input\PathInput::withIOFields(...$ioFields);
    }

    private function processInputs(Operation $operation, string $httpMethod, Input ...$inputs): void
    {
        foreach ($inputs as $input) {
            $this->inputDescriber->describe($operation, $input, $httpMethod);
        }
    }

    private function processResponses(
        Operation $operation,
        \Speicher210\OpenApiGenerator\Model\Response ...$responsesConfig
    ): void {
        Assert::isInstanceOf($operation->responses, Responses::class);

        foreach ($responsesConfig as $response) {
            $responseData = [
                'description' => $response->description(),
            ];

            $output = $response->output();
            if ($output !== null) {
                $responseData['content'] = [
                    $output->contentType() => [
                        'schema' => $this->outputDescriber->describe($output),
                    ],
                ];
            }

            $operation->responses->addResponse(
                (string) $response->statusCode(),
                new Response($responseData)
            );
        }
    }

    public function canProcess(Path $path): bool
    {
        return $path instanceof SymfonyRoutePath;
    }
}
