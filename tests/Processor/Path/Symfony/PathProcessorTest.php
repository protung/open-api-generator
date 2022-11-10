<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Processor\Path\Symfony;

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
use Protung\OpenApiGenerator\Model;
use Protung\OpenApiGenerator\Model\ModelRegistry;
use Protung\OpenApiGenerator\Model\Path\Input;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Path\PathOperation;
use Protung\OpenApiGenerator\Model\Response;
use Protung\OpenApiGenerator\Model\Type;
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
            'test_api.test',
            'API',
            'Test info.',
            null,
            [],
            [],
        );

        $routeCollectionMock = $this->createMock(RouteCollection::class);
        $routeCollectionMock
            ->expects(self::once())
            ->method('get')
            ->with($path->routeName())
            ->willReturn(new Route('/api/test'));

        $pathProcessor = new PathProcessor($routeCollectionMock, $operationDescriber);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No HTTP methods defined for route "test_api.test".');
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
            ->method('describe')
            ->withConsecutive(
                [
                    Input\BodyInput::withIOFields(IOField::stringField('simpleString')),
                ],
                [
                    Input\PathInput::withIOFields(
                        IOField::stringField('str'),
                        IOField::stringField('enum')->withPossibleValues(['a', 'b']),
                    ),
                ],
            );

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
            'api_test_get',
            'Test',
            'Test post with simple object input and output',
            'Test post with simple object input and output',
            [
                Input\BodyInput::withIOFields(
                    IOField::stringField('simpleString'),
                ),
            ],
            [
                Response::for200(Model\Path\Output\ScalarOutput::plainText(Type::STRING)),
            ],
        );

        $routeCollectionMock = $this->createMock(RouteCollection::class);
        $routeCollectionMock
            ->expects(self::once())
            ->method('get')
            ->with($path->routeName())
            ->willReturn(
                new Route(
                    '/api/test/{str}/{enum}',
                    requirements: ['enum' => 'a|b'],
                    methods: ['GET'],
                ),
            );

        $pathProcessor = new PathProcessor($routeCollectionMock, $operationDescriber);

        $actual = $pathProcessor->process($path);

        $expected = new PathOperation(
            'get',
            '/api/test/{str}/{enum}',
            new Operation(
                [
                    'tags' => ['Test'],
                    'summary' => 'Test post with simple object input and output',
                    'description' => 'Test post with simple object input and output',
                    'parameters' => [],
                    'responses' => new Responses(
                        [
                            '200' => new \cebe\openapi\spec\Response(
                                [
                                    'description' => 'Returned on success',
                                    'content' => [
                                        'text/plain' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'string',
                                                        'example' => 'string',
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

        self::assertEquals([$expected], $actual);
    }
}
