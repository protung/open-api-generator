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
            'api_test_post_simple_object_input_and_output',
            'Test',
            'Test post with simple object input and output',
            'Test post with simple object input and output',
            [
                Input\BodyInput::withIOFields(
                    IOField::stringField('simpleString'),
                    IOField::arrayField(
                        'simpleArrayOfStrings',
                        IOField::stringField('simpleString'),
                    ),
                    IOField::arrayField(
                        'simpleNullableArrayOfStrings',
                        IOField::stringField('simpleString'),
                    )->asNullable(),
                    IOField::arrayField(
                        'simpleArrayOfObjects',
                        IOField::objectField(
                            'simpleObjectInArray',
                            IOField::booleanField('simpleBoolean'),
                            IOField::stringField('simpleString'),
                            IOField::integerField('simpleInteger'),
                            IOField::objectField(
                                'simpleInnerObject',
                                IOField::booleanField('simpleBoolean'),
                                IOField::stringField('simpleString'),
                                IOField::integerField('simpleInteger'),
                            ),
                        ),
                    ),
                    IOField::objectField(
                        'simpleObject',
                        IOField::booleanField('simpleBoolean'),
                        IOField::booleanField('simpleNullableBoolean')->asNullable(),
                        IOField::stringField('simpleString'),
                        IOField::stringField('simpleNullableString')->asNullable(),
                        IOField::integerField('simpleInteger'),
                        IOField::integerField('simpleNullableInteger')->asNullable(),
                        IOField::objectField(
                            'simpleInnerObject',
                            IOField::booleanField('simpleBoolean'),
                            IOField::stringField('simpleString'),
                            IOField::integerField('simpleInteger'),
                        ),
                        IOField::objectField('simpleNullableObjectWithoutChildren')->asNullable(),
                    ),
                    IOField::objectField(
                        'simpleNullableObjectWithChildren',
                        IOField::booleanField('simpleBoolean'),
                    )->asNullable(),
                ),
            ],
            [
                Response::for200(Model\Path\Output\ScalarOutput::plainText(Type::STRING)),
                Response::for201(Model\Path\Output\ScalarOutput::json(Type::INTEGER)),
                Response::for202(),
                new Response(203, [], Model\Path\Output\ScalarOutput::json(Type::NUMBER)),
                new Response(204, [], Model\Path\Output\ScalarOutput::json(Type::BOOLEAN)->withExample(false)),
                new Response(
                    205,
                    [],
                    Model\Path\Output\SimpleOutput::fromIOFields(
                        IOField::unknown('myUnknown'),
                        IOField::anything('myAnything'),
                        IOField::integerField('myInt')->withExample(42),
                        IOField::stringField('myString')->withExample('ms'),
                        IOField::booleanField('myBoolean')->withExample(false),
                        IOField::numberField('myChoice')->withPossibleValues([1, 2, 3]),
                        IOField::anything('optional')->asOptional(),
                    ),
                ),
                new Response(
                    206,
                    [],
                    Model\Path\Output\CollectionOutput::forOutput(Model\Path\Output\ScalarOutput::plainText(Type::STRING)),
                ),
                new Response(
                    210,
                    ['multiple outputs'],
                    Model\Path\Output\FileOutput::forHtml(),
                    Model\Path\Output\FileOutput::forJpeg(),
                    Model\Path\Output\FileOutput::forPdf(),
                    Model\Path\Output\FileOutput::forPlainText(),
                    Model\Path\Output\FileOutput::forPng(),
                    Model\Path\Output\FileOutput::forZip(),
                    Model\Path\Output\ScalarOutput::json(Type::INTEGER),
                ),
                new Response(
                    212,
                    ['from example data'],
                    Model\Path\Output\SimpleOutput::fromExampleData(
                        [
                            'myUnknown' => null,
                            'myInt' => 42,
                            'myFloat' => 3.1415,
                            'myString' => 'ms',
                            'myBoolean' => false,
                            'myObject' => [
                                'myUnknown' => null,
                                'myInt' => -42,
                                'myFloat' => -3.1415,
                                'myString' => 'sm',
                                'myBoolean' => true,
                            ],
                            'myIntegerCollection' => [1, 2, 3],
                            'myFloatCollection' => [1.1, 2.2, 3.3],
                            'myBooleanCollection' => [true, false],
                            'myStringCollection' => ['a', 'b', 'c'],
                            'myObjectCollection' => [
                                [
                                    'myUnknown' => null,
                                    'myInt' => -42,
                                    'myFloat' => -3.1415,
                                    'myString' => 'sm',
                                    'myBoolean' => true,
                                ],
                            ],
                            'myArrayCollection' => [
                                [1],
                            ],
                        ],
                    ),
                ),
            ],
        );

        $routeCollectionMock = $this->createMock(RouteCollection::class);
        $routeCollectionMock
            ->expects(self::once())
            ->method('get')
            ->with($path->routeName())
            ->willReturn(new Route('/api/test', methods: ['GET']));

        $pathProcessor = new PathProcessor($routeCollectionMock, $operationDescriber);

        $actual = $pathProcessor->process($path);

        $expected = new PathOperation(
            'get',
            '/api/test',
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
                            '201' => new \cebe\openapi\spec\Response(
                                [
                                    'description' => 'Returned on success',
                                    'content' => [
                                        'application/json' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'integer',
                                                        'example' => 123,
                                                    ],
                                                ),
                                            ],
                                        ),
                                    ],
                                ],
                            ),
                            '202' => new \cebe\openapi\spec\Response(
                                ['description' => 'Returned when successfully accepted data'],
                            ),
                            '203' => new \cebe\openapi\spec\Response(
                                [
                                    'description' => '',
                                    'content' => [
                                        'application/json' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'number',
                                                        'example' => 3.14,
                                                    ],
                                                ),
                                            ],
                                        ),
                                    ],
                                ],
                            ),
                            '204' => new \cebe\openapi\spec\Response(
                                [
                                    'description' => '',
                                    'content' => [
                                        'application/json' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'boolean',
                                                        'example' => false,
                                                    ],
                                                ),
                                            ],
                                        ),
                                    ],
                                ],
                            ),
                            '205' => new \cebe\openapi\spec\Response(
                                [
                                    'description' => '',
                                    'content' => [
                                        'application/json' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'required' => [
                                                            'myUnknown',
                                                            'myAnything',
                                                            'myInt',
                                                            'myString',
                                                            'myBoolean',
                                                            'myChoice',

                                                        ],
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'myUnknown' => new Schema([]),
                                                            'myAnything' => new Schema(['type' => 'any']),
                                                            'myInt' => new Schema([
                                                                'type' => 'integer',
                                                                'example' => 42,
                                                            ]),
                                                            'myString' => new Schema([
                                                                'type' => 'string',
                                                                'example' => 'ms',
                                                            ]),
                                                            'myBoolean' => new Schema([
                                                                'type' => 'boolean',
                                                                'example' => false,
                                                            ]),
                                                            'myChoice' => new Schema([
                                                                'enum' => [1, 2, 3],
                                                                'type' => 'number',
                                                            ]),
                                                            'optional' => new Schema(['type' => 'any']),
                                                        ],
                                                        'example' => [
                                                            'myUnknown' => null,
                                                            'myAnything' => null,
                                                            'myInt' => 123,
                                                            'myString' => 'string',
                                                            'myBoolean' => true,
                                                            'myChoice' => 1,
                                                            'optional' => null,

                                                        ],
                                                    ],
                                                ),
                                            ],
                                        ),
                                    ],
                                ],
                            ),
                            '206' => new \cebe\openapi\spec\Response(
                                [
                                    'description' => '',
                                    'content' => [
                                        'text/plain' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'array',
                                                        'items' => new Schema([
                                                            'type' => 'string',
                                                            'example' => 'string',
                                                        ]),
                                                    ],
                                                ),
                                            ],
                                        ),
                                    ],
                                ],
                            ),
                            '210' => new \cebe\openapi\spec\Response(
                                [
                                    'description' => 'multiple outputs',
                                    'content' => [
                                        'text/html' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'string',
                                                        'format' => 'binary',
                                                    ],
                                                ),
                                            ],
                                        ),
                                        'image/jpeg' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'string',
                                                        'format' => 'binary',
                                                    ],
                                                ),
                                            ],
                                        ),
                                        'application/pdf' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'string',
                                                        'format' => 'binary',
                                                    ],
                                                ),
                                            ],
                                        ),
                                        'text/plain' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'string',
                                                        'format' => 'binary',
                                                    ],
                                                ),
                                            ],
                                        ),
                                        'image/png' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'string',
                                                        'format' => 'binary',
                                                    ],
                                                ),
                                            ],
                                        ),
                                        'application/zip' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'string',
                                                        'format' => 'binary',
                                                    ],
                                                ),
                                            ],
                                        ),
                                        'application/json' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'type' => 'integer',
                                                        'example' => '123',
                                                    ],
                                                ),
                                            ],
                                        ),
                                    ],
                                ],
                            ),
                            '212' => new \cebe\openapi\spec\Response(
                                [
                                    'description' => 'from example data',
                                    'content' => [
                                        'application/json' => new MediaType(
                                            [
                                                'schema' => new Schema(
                                                    [
                                                        'required' => [
                                                            'myUnknown',
                                                            'myInt',
                                                            'myFloat',
                                                            'myString',
                                                            'myBoolean',
                                                            'myObject',
                                                            'myIntegerCollection',
                                                            'myFloatCollection',
                                                            'myBooleanCollection',
                                                            'myStringCollection',
                                                            'myObjectCollection',
                                                            'myArrayCollection',

                                                        ],
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'myUnknown' => new Schema(['nullable' => true]),
                                                            'myInt' => new Schema(['type' => 'integer']),
                                                            'myFloat' => new Schema(['type' => 'number']),
                                                            'myString' => new Schema(['type' => 'string']),
                                                            'myBoolean' => new Schema(['type' => 'boolean']),
                                                            'myObject' => new Schema([
                                                                'required' => [
                                                                    'myUnknown',
                                                                    'myInt',
                                                                    'myFloat',
                                                                    'myString',
                                                                    'myBoolean',
                                                                ],
                                                                'type' => 'object',
                                                                'properties' => [
                                                                    'myUnknown' => new Schema(['nullable' => true]),
                                                                    'myInt' => new Schema(['type' => 'integer']),
                                                                    'myFloat' => new Schema(['type' => 'number']),
                                                                    'myString' => new Schema(['type' => 'string']),
                                                                    'myBoolean' => new Schema(['type' => 'boolean']),
                                                                ],
                                                            ]),
                                                            'myIntegerCollection' => new Schema([
                                                                'type' => 'array',
                                                                'items' => new Schema(['type' => 'integer']),
                                                            ]),
                                                            'myFloatCollection' => new Schema([
                                                                'type' => 'array',
                                                                'items' => new Schema(['type' => 'number']),
                                                            ]),
                                                            'myBooleanCollection' => new Schema([
                                                                'type' => 'array',
                                                                'items' => new Schema(['type' => 'boolean']),
                                                            ]),
                                                            'myStringCollection' => new Schema([
                                                                'type' => 'array',
                                                                'items' => new Schema(['type' => 'string']),
                                                            ]),
                                                            'myObjectCollection' => new Schema([
                                                                'type' => 'array',
                                                                'items' => new Schema([
                                                                    'required' => [
                                                                        'myUnknown',
                                                                        'myInt',
                                                                        'myFloat',
                                                                        'myString',
                                                                        'myBoolean',
                                                                    ],
                                                                    'type' => 'object',
                                                                    'properties' => [
                                                                        'myUnknown' => new Schema(['nullable' => true]),
                                                                        'myInt' => new Schema(['type' => 'integer']),
                                                                        'myFloat' => new Schema(['type' => 'number']),
                                                                        'myString' => new Schema(['type' => 'string']),
                                                                        'myBoolean' => new Schema(['type' => 'boolean']),
                                                                    ],
                                                                ]),
                                                            ]),
                                                            'myArrayCollection' => new Schema([
                                                                'type' => 'array',
                                                                'items' => new Schema(['type' => 'array']),
                                                            ]),
                                                        ],
                                                        'example' => [
                                                            'myUnknown' => null,
                                                            'myInt' => 42,
                                                            'myFloat' => 3.1415,
                                                            'myString' => 'ms',
                                                            'myBoolean' => false,
                                                            'myObject' => [
                                                                'myUnknown' => null,
                                                                'myInt' => -42,
                                                                'myFloat' => -3.1415,
                                                                'myString' => 'sm',
                                                                'myBoolean' => true,
                                                            ],
                                                            'myIntegerCollection' => [
                                                                1,
                                                                2,
                                                                3,
                                                            ],
                                                            'myFloatCollection' => [
                                                                1.1,
                                                                2.2,
                                                                3.3,
                                                            ],
                                                            'myBooleanCollection' => [
                                                                true,
                                                                false,
                                                            ],
                                                            'myStringCollection' => [
                                                                'a',
                                                                'b',
                                                                'c',
                                                            ],
                                                            'myObjectCollection' => [
                                                                [
                                                                    'myUnknown' => null,
                                                                    'myInt' => -42,
                                                                    'myFloat' => -3.1415,
                                                                    'myString' => 'sm',
                                                                    'myBoolean' => true,
                                                                ],
                                                            ],
                                                            'myArrayCollection' => [
                                                                [1],
                                                            ],
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

        self::assertEquals([$expected], $actual);
    }
}
