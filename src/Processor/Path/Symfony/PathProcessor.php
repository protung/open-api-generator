<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Processor\Path\Symfony;

use InvalidArgumentException;
use Override;
use Protung\OpenApiGenerator\Describer\OperationDescriber;
use Protung\OpenApiGenerator\Model\Path\Input;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Path\Path;
use Protung\OpenApiGenerator\Model\Path\PathOperation;
use Protung\OpenApiGenerator\Processor\Path\PathProcessor as PathProcessorInterface;
use Psl;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

use function explode;
use function in_array;
use function str_contains;

final class PathProcessor implements PathProcessorInterface
{
    /** Route requirements matching whole numbers only, including the hand written spellings of the Symfony constants. */
    private const NUMERIC_REQUIREMENTS = [Requirement::DIGITS, Requirement::POSITIVE_INT, '\\d+', '[1-9]\\d*'];

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
    #[Override]
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
        if ($route->getMethods() === []) {
            throw new InvalidArgumentException(
                Psl\Str\format('No HTTP methods defined for route "%s".', $path->routeName()),
            );
        }

        $path->addInput($this->extractInputFromRoute($route));

        $operations = [];
        foreach ($route->getMethods() as $method) {
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
        foreach ($route->compile()->getPathVariables() as $routePathVariable) {
            $pathVariable = Psl\Type\string()->coerce($routePathVariable);

            $requirement = $route->getRequirement($pathVariable);
            if ($requirement === null) {
                $ioFields[] = IOField::stringField($pathVariable);
            } elseif (str_contains($requirement, '|')) {
                $ioFields[] = IOField::stringField($pathVariable)->withPossibleValues(explode('|', $requirement));
            } elseif (in_array($requirement, self::NUMERIC_REQUIREMENTS, true)) {
                $ioFields[] = IOField::integerField($pathVariable);
            } else {
                $ioFields[] = IOField::stringField($pathVariable)->withPattern($requirement);
            }
        }

        return Input\PathInput::withIOFields(...$ioFields);
    }

    #[Override]
    public function canProcess(Path $path): bool
    {
        return $path instanceof SymfonyRoutePath;
    }
}
