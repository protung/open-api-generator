<?php

declare(strict_types=1);

use Speicher210\OpenApiGenerator\Model;
use Speicher210\OpenApiGenerator\Model\Callback;
use Speicher210\OpenApiGenerator\Model\FormDefinition;
use Speicher210\OpenApiGenerator\Model\Info\Info;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use Speicher210\OpenApiGenerator\Model\Path\Input\FormInput;
use Speicher210\OpenApiGenerator\Model\Path\IOField;
use Speicher210\OpenApiGenerator\Model\Path\Output\ObjectOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\PaginatedOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\RFC7807ErrorOutput;
use Speicher210\OpenApiGenerator\Model\Response;
use Speicher210\OpenApiGenerator\Model\Type;
use Speicher210\OpenApiGenerator\Processor\Path;
use Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration;

return new Model\Specification(
    new Info(
        'Open API Generator',
        'To specify the API version use header: `X-Accept-Version: 1.1.0`',
    ),
    [
        Model\Security\Definition::apiKey('ApiKey', 'X-API-KEY', 'Value for the X-API-KEY header'),
        Model\Security\Definition::basicAuth('basic-auth', 'The basic auth'),
        Model\Security\Definition::bearerAuth('bearer-key', 'JWT', 'The bearer auth'),
    ],
    [
        new Path\Symfony\SymfonyRoutePath(
            'api_test_headers',
            'Test',
            'Test headers.',
            null,
            [Input\HeaderInput::withName('X-API-VERSION')],
            [],
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_get_one_item',
            'Test',
            'Test get one item.',
            null,
            [],
            [
                Response::for200(
                    ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ComplexObject::class)
                ),
                Response::for401(),
            ],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_custom_query_params',
            'Test',
            'Test custom query params',
            null,
            [
                FormInput::inQuery(new FormDefinition(TestSchemaGeneration\Form\QueryType::class)),
                Input\QueryInput::withIOField(IOField::stringField('custom_query_string_field')->withExample('sf')),
                Input\QueryInput::withIOField(IOField::integerField('custom_query_integer_field')->withExample(42)),
                Input\QueryInput::withIOField(IOField::booleanField('custom_query_boolean_field')->withExample(true)),
            ],
            [],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_custom_path_params',
            'Test',
            'Test custom path params',
            null,
            [],
            [],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_custom_responses',
            'Test',
            'Test custom responses',
            'There are a lot of possible responses but no security',
            [],
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
                    )
                ),
                new Response(
                    206,
                    [],
                    Model\Path\Output\CollectionOutput::forOutput(Model\Path\Output\ScalarOutput::plainText(Type::STRING))
                ),
                new Response(207, [], ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ComplexObject::class)),
                new Response(
                    208,
                    [],
                    Model\Path\Output\CollectionOutput::forClass(TestSchemaGeneration\Model\JMS\ComplexObject::class)
                ),
                new Response(
                    209,
                    [],
                    ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\InlineArrayOfObjects::class)
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
                    Model\Path\Output\ScalarOutput::json(Type::INTEGER)
                ),
                new Response(
                    211,
                    ['output with discriminator'],
                    ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\DiscriminatorParentObject::class),
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
                        ]
                    )
                ),
            ],
            null,
            true
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_custom_error_responses',
            'Test',
            'Test custom error responses',
            null,
            [],
            [
                Response::for400(RFC7807ErrorOutput::create(
                    400,
                    'something custom'
                ))->withDescription(['Custom message 400']),
                Response::for401(),
                Response::for402(),
                Response::for403()->withDescription(['Custom message 403']),
                Response::for404()->withDescription(['Custom message 404', 'Another custom message 404']),
                Response::for405(),
                Response::for406(),
                Response::for409(),
                Response::for415(),
                Response::for423(),
                new Response(418, ['Teapot without output']),
                new Response(
                    428,
                    ['Custom precondition'],
                    Model\Path\Output\SimpleOutput::fromIOFields(Model\Path\IOField::stringField('precondition'))
                ),
            ],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_custom_polymorphic_responses',
            'Test',
            'Test custom polymorphic responses',
            null,
            [],
            [
                Response::for400(
                    Model\Path\Output\ScalarOutput::plainText(Type::STRING),
                    Model\Path\Output\ScalarOutput::plainText(Type::INTEGER),
                    Model\Path\Output\ScalarOutput::json(Type::STRING),
                    Model\Path\Output\ScalarOutput::json(Type::INTEGER),
                    RFC7807ErrorOutput::create(400, 'Test')
                ),
            ],
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_paginated_response',
            'Test',
            'Test paginated response',
            null,
            [],
            [
                Response::for200(
                    new PaginatedOutput(
                        'multiple_types',
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ComplexObject::class),
                        ObjectOutput::withSerializationGroups(
                            TestSchemaGeneration\Model\JMS\ComplexObject::class,
                            ['Test']
                        ),
                        ObjectOutput::forClass(TestSchemaGeneration\Model\NotDescribedObject::class),
                    )
                ),
                Response::for201(
                    new PaginatedOutput(
                        'one_type',
                        Model\Path\Output\SimpleOutput::fromIOFields(Model\Path\IOField::objectField('someField'))
                    )
                ),
                new Response(
                    202,
                    ['Children with discriminator'],
                    new PaginatedOutput(
                        'children_with_discriminator',
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\DiscriminatorFirstChildObject::class),
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\DiscriminatorSecondChildObject::class),
                    )
                ),
                new Response(
                    203,
                    ['Parent class with discriminator'],
                    new PaginatedOutput(
                        'parent_with_discriminator',
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\DiscriminatorParentObject::class),
                    )
                ),
                Response::for204(),
            ],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_referenced_response',
            'Test',
            'Test referenced response',
            null,
            [],
            [
                Response::for200(
                    Model\Path\Output\ReferencableOutput::forSchema(
                    // JMSObject is also used as not referenced.
                    // We want to make sure this is only referenced for this path.
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ComplexObject::class)
                    )
                ),
                Response::for201(
                    Model\Path\Output\ReferencableOutput::forSchema(
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ObjectDescribedOnlyAsReference::class),
                        'JMSObjectDescribedOnlyAsReferenceCustomName'
                    ),
                ),
                Response::for400(
                // We want to test that using the same name will not throw an error is the definition matches.
                    Model\Path\Output\ReferencableOutput::forSchema(
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ObjectDescribedOnlyAsReference::class),
                        'JMSObjectDescribedOnlyAsReferenceCustomName'
                    ),
                ),
            ]
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_multiple_methods',
            'Test',
            'Test multiple methods',
            null,
            [],
            [],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_post_with_form',
            'Test',
            'Test post with form',
            null,
            [
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestType::class)),
            ],
            [
                Response::for400WithForm(TestSchemaGeneration\Form\TestType::class),
            ],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_patch_with_form',
            'Test',
            'Test patch with form',
            null,
            [
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestType::class)),
            ],
            [
                Response::for400WithForm(TestSchemaGeneration\Form\TestType::class),
            ],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_post_with_file_upload',
            'Test',
            'Test post with file upload',
            null,
            [
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestFileUpload::class)),
            ],
            [
                Response::for400WithForm(TestSchemaGeneration\Form\TestFileUpload::class),
            ],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_post_with_file_upload_optional',
            'Test',
            'Test post with file upload optional',
            null,
            [
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestFileUploadOptional::class)),
            ],
            [
                Response::for400WithForm(TestSchemaGeneration\Form\TestFileUploadOptional::class),
            ],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_post_with_form_with_data_class',
            'Test',
            'Test post with data class form',
            null,
            [
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestDataClassType::class)),
            ],
            [
                Response::for400WithForm(TestSchemaGeneration\Form\TestDataClassType::class),
            ],
            Model\Security\Reference::fromString('ApiKey')
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_post_with_multiple_form_inputs',
            'Test',
            'Test post with multiple form inputs',
            null,
            [
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestInnerType::class)),
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestDataClassType::class)),
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestFileUpload::class)),
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestFileUploadOptional::class)),
            ],
            [],
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_get_not_described_object',
            'Test',
            'Test get a not described object',
            null,
            [],
            [
                Response::for200(
                    ObjectOutput::forClass(TestSchemaGeneration\Model\NotDescribedObject::class),
                ),
            ],
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_get_output_object_example',
            'Test',
            'Test get an output example from an object',
            null,
            [],
            [
                Response::for200(
                    ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ComplexObject::class)
                        ->withExample(TestSchemaGeneration\Model\JMS\ComplexObjectExampleBuilder::create()),
                ),
                Response::for201(
                    ObjectOutput::withSerializationGroups(TestSchemaGeneration\Model\JMS\ComplexObject::class, ['Test'])
                        ->withExample(TestSchemaGeneration\Model\JMS\ComplexObjectExampleBuilder::create()),
                ),
                new Response(
                    202,
                    [],
                    Model\Path\Output\CollectionOutput::forOutput(
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ComplexObject::class)
                            ->withExample(TestSchemaGeneration\Model\JMS\ComplexObjectExampleBuilder::create())
                    )
                ),
            ],
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_get_with_file_output',
            'Test',
            'Test get with a file output',
            null,
            [],
            [
                Response::for200(
                    Model\Path\Output\FileOutput::forPdf(),
                ),
            ],
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_callbacks',
            'Test',
            'Test callbacks',
            null,
            [],
            [],
            null,
            false,
            [
                new Callback(
                    'eventName',
                    '{$request.body#/callbackUrl}',
                    'GET',
                    new Callback\Path(
                        'Callback tag',
                        'Callback summary',
                        'Callback description',
                        [
                            FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestType::class)),
                        ],
                        [
                            Response::for200(
                                ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ComplexObject::class)
                            ),
                        ],
                    ),
                ),
            ]
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_post_simple_object_input_and_output',
            'Test',
            'Test post with simple object input and output',
            null,
            [
                Input\BodyInput::withIOFields(
                    IOField::stringField('simpleString'),
                    IOField::arrayField(
                        'simpleArrayOfStrings',
                        IOField::stringField('simpleString'),
                    ),
                    IOField::arrayField(
                        'simpleNullableArrayOfStrings',
                        IOField::stringField('simpleString')
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
                            )
                        )
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
                        IOField::booleanField('simpleBoolean')
                    )->asNullable()
                ),
            ],
            [
                Response::for200(
                    Model\Path\Output\SimpleOutput::fromIOFields(
                        IOField::unknown('simpleUnknown'),
                        IOField::anything('simpleAnything'),
                        IOField::stringField('simpleString'),
                        IOField::objectField(
                            'simpleObject',
                            IOField::booleanField('simpleBoolean'),
                            IOField::stringField('simpleString'),
                            IOField::integerField('simpleInteger'),
                            IOField::objectField(
                                'simpleInnerObject',
                                IOField::booleanField('simpleBoolean'),
                                IOField::stringField('simpleString'),
                                IOField::integerField('simpleInteger'),
                            )
                        )
                    ),
                ),
            ],
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_nullability',
            'Test',
            'Test nullability',
            null,
            [
                FormInput::inQuery(new FormDefinition(TestSchemaGeneration\Form\TestNullabilityType::class)),
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestNullabilityType::class)),
            ],
            [],
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_required',
            'Test',
            'Test required params',
            null,
            [
                FormInput::inQuery(new FormDefinition(TestSchemaGeneration\Form\TestRequiredType::class)),
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestRequiredType::class)),
            ],
            [],
        ),
        new Path\Symfony\SymfonyRoutePath(
            'api_test_form_definition_options',
            'Test',
            'Test forms with definition options',
            null,
            [
                FormInput::inQuery(new FormDefinition(TestSchemaGeneration\Form\TestFormDefinitionOptions::class, ['form_option' => 42])),
                FormInput::inBody(new FormDefinition(TestSchemaGeneration\Form\TestFormDefinitionOptions::class, ['form_option' => 42])),
            ],
            [],
        ),
    ],
    [
        Input\HeaderInput::withName('X-ALWAYS'),
    ],
    [
        Response::for500(),
    ]
);
