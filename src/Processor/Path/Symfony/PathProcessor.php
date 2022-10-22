<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Processor\Path\Symfony;

use InvalidArgumentException;
use Protung\OpenApiGenerator\Describer\OperationDescriber;
use Protung\OpenApiGenerator\Model\Path\Input;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Path\Path;
use Protung\OpenApiGenerator\Model\Path\PathOperation;
use Protung\OpenApiGenerator\Processor\Path\PathProcessor as PathProcessorInterface;
use Psl;
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
                Psl\Str\format('Defined "%s" route in API doc configuration does not exist.', $path->routeName()),
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
                $operation,
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
