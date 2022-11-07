<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Processor\Symfony;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
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
use Protung\OpenApiGenerator\Model\Path\PathOperation;
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
        $this->expectExceptionMessage('No HTTP methods defined for route "test_api.application.ping".');
        $pathProcessor->process($path);
    }

    public function testProcessRoute(): void
    {
        $inputDescriberMock = $this->createMock(InputDescriber\InputDescriber::class);
        $inputDescriberMock
            ->expects(self::exactly(2))
            ->method('supports')
            ->willReturn(true);
        $inputDescriberMock
            ->expects(self::exactly(2))
            ->method('describe');

        $operationDescriber = new OperationDescriber(
            new InputDescriber($inputDescriberMock),
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
            ->willReturn(new Route('/api/ping', methods: ['GET']));

        $pathProcessor = new PathProcessor($routeCollectionMock, $operationDescriber);

        $actual   = $pathProcessor->process($path);
        $expected = new PathOperation(
            'get',
            '/api/ping',
            new Operation(
                [
                    'tags' => ['API'],
                    'summary' => 'Get information about the API.',
                    'parameters' => [],
                    'responses' => new Responses(
                        [
                            '401' => new \cebe\openapi\spec\Response(
                                [
                                    'description' => 'Authentication is missing, invalid or expired',
                                    'content' => [
                                        'application/problem+json' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'required' => ['type', 'title', 'status', 'detail'],
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'type' => new Schema(
                                                                ['type' => 'string'],
                                                            ),
                                                            'title' => new Schema(
                                                                ['type' => 'string'],
                                                            ),
                                                            'status' => new Schema(
                                                                ['type' => 'integer'],
                                                            ),
                                                            'detail' => new Schema(
                                                                ['type' => 'string'],
                                                            ),
                                                        ],
                                                        'example' => [
                                                            'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
                                                            'title' => 'An error occurred',
                                                            'status' => 401,
                                                            'detail' => 'Unauthorized',
                                                        ],
                                                    ],
                                                ),
                                            ],
                                        ),
                                    ],
                                ],
                            ),
                        ],
                    ),
                    'security' => [],
                ],
            ),
        );

        self::assertCount(1, $actual);
        self::assertEquals($expected, $actual[0]);
    }
}
