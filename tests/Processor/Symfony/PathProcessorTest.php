<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Processor\Symfony;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Describer\ExampleDescriber\ExampleDescriber;
use Protung\OpenApiGenerator\Describer\Form\FormFactory;
use Protung\OpenApiGenerator\Describer\InputDescriber;
use Protung\OpenApiGenerator\Describer\ObjectDescriber;
use Protung\OpenApiGenerator\Describer\OperationDescriber;
use Protung\OpenApiGenerator\Describer\OutputDescriber;
use Protung\OpenApiGenerator\Model\ModelRegistry;
use Protung\OpenApiGenerator\Model\Path\Input\HeaderInput;
use Protung\OpenApiGenerator\Model\Response;
use Protung\OpenApiGenerator\Processor\Path\Symfony\PathProcessor;
use Protung\OpenApiGenerator\Processor\Path\Symfony\SymfonyRoutePath;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class PathProcessorTest extends TestCase
{
    public function testProcessRouteExceptionThrownIfMethodsNotDefined(): void
    {
        $operationDescriber = new OperationDescriber(
            new InputDescriber(
                $this->createMock(InputDescriber\InputDescriber::class),
            ),
            new OutputDescriber(
                new ObjectDescriber(
                    new ModelRegistry(),
                    $this->createMock(ObjectDescriber\Describer::class),
                ),
                new FormFactory($this->createMock(FormFactoryInterface::class)),
                $this->createMock(ExampleDescriber::class),
            ),
        );

        $path = new SymfonyRoutePath(
            'test_api.application.ping',
            'API',
            'Get information about the API.',
            null,
            [HeaderInput::withName('test')],
            [Response::for401()],
        );

        $routeCollectionMock = $this->createMock(RouteCollection::class);
        $routeCollectionMock
            ->expects(self::once())
            ->method('get')
            ->with($path->routeName())
            ->willReturn(new Route('/api/ping'));

        $pathProcessor = new PathProcessor($routeCollectionMock, $operationDescriber);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The defined methods for the "test_api.application.ping" route do not exist in the API doc configuration.');
        $pathProcessor->process($path);
    }
}
