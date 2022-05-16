<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor\Path\Symfony;

use InvalidArgumentException;
use Psl;
use Speicher210\OpenApiGenerator\Describer\OperationDescriber;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use Speicher210\OpenApiGenerator\Model\Path\IOField;
use Speicher210\OpenApiGenerator\Model\Path\Path;
use Speicher210\OpenApiGenerator\Model\Path\PathOperation;
use Speicher210\OpenApiGenerator\Processor\Path\PathProcessor as PathProcessorInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

use function explode;
use function str_contains;

final class PathProcessor implements PathProcessorInterface
{
    private RouteCollection $routeCollection;

    private OperationDescriber $operationDescriber;

    public function __construct(RouteCollection $routeCollection, OperationDescriber $operationDescriber)
    {
        $this->routeCollection    = $routeCollection;
        $this->operationDescriber = $operationDescriber;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Path $path): array
    {
        $path = Psl\Type\instance_of(SymfonyRoutePath::class)->coerce($path);

        $symfonyRoute = $this->routeCollection->get($path->routeName());

        if ($symfonyRoute === null) {
            throw new InvalidArgumentException(
                Psl\Str\format('Defined "%s" route in API doc configuration does not exist.', $path->routeName())
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
            $path->addInput($this->extractInputFromRoute($route));

            $operation = $this->operationDescriber->describe($method, $path);

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
            $field = IOField::stringField($pathVariable);

            $requirement = $route->getRequirement($pathVariable);
            if ($requirement !== null) {
                if (str_contains($requirement, '|')) {
                    $field->withPossibleValues(explode('|', $requirement));
                } else {
                    $field->withPattern($requirement);
                }
            }

            $ioFields[] = $field;
        }

        return Input\PathInput::withIOFields(...$ioFields);
    }

    public function canProcess(Path $path): bool
    {
        return $path instanceof SymfonyRoutePath;
    }
}
