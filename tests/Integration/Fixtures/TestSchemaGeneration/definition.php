<?php

declare(strict_types=1);

use Speicher210\OpenApiGenerator\Model;
use Speicher210\OpenApiGenerator\Model\FormDefinition;
use Speicher210\OpenApiGenerator\Model\Info\Info;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use Speicher210\OpenApiGenerator\Model\Path\Input\FormInput;
use Speicher210\OpenApiGenerator\Model\Path\IOField;
use Speicher210\OpenApiGenerator\Model\Path\Output\ObjectOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\PaginatedOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\RFC7807ErrorResponse;
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
                    ObjectOutput::forClass(TestSchemaGeneration\Model\JMSObject::class)
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
                new FormInput(
                    new FormDefinition(TestSchemaGeneration\Form\QueryType::class),
                    Input::LOCATION_QUERY
                ),
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
                Response::for200(new Model\Path\Output\ScalarOutput(Type::STRING)),
                Response::for201(new Model\Path\Output\ScalarOutput(Type::INTEGER)),
                Response::for202(),
                new Response(203, [], new Model\Path\Output\ScalarOutput(Type::NUMBER)),
                new Response(204, [], new Model\Path\Output\ScalarOutput(Type::BOOLEAN)),
                new Response(
                    205,
                    [],
                    new Model\Path\Output\SimpleOutput(
                        IOField::integerField('myInt'),
                        IOField::stringField('myString'),
                        new IOField('myChoice', Type::NUMBER, null, [1, 2, 3]),
                    )
                ),
                new Response(206, [], new Model\Path\Output\CollectionOutput(
                    new Model\Path\Output\ScalarOutput(Type::STRING)
                )),
                new Response(207, [], ObjectOutput::forClass(TestSchemaGeneration\Model\JMSObject::class)),
                new Response(208, [], new Model\Path\Output\CollectionOutput(
                    ObjectOutput::forClass(TestSchemaGeneration\Model\JMSObject::class)
                )),
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
                Response::for400(new RFC7807ErrorResponse(400, 'something custom'), ['Custom message 400']),
                Response::for401(),
                Response::for402(['Custom message 402']),
                Response::for403(['Custom message 403']),
                Response::for404(['Custom message 404', 'Another custom message 404']),
                Response::for406(),
                Response::for415(),
                new Response(418, ['Teapot without output']),
                new Response(
                    428,
                    ['Custom precondition'],
                    new Model\Path\Output\SimpleOutput(new Model\Path\IOField('precondition', Model\Type::STRING))
                ),
            ],
            Model\Security\Reference::fromString('ApiKey')
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
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMSObject::class),
                        ObjectOutput::withSerializationGroups(TestSchemaGeneration\Model\JMSObject::class, ['Test']),
                    )
                ),
                Response::for201(
                    new PaginatedOutput(
                        'one_type',
                        new Model\Path\Output\SimpleOutput(new Model\Path\IOField('someField', Model\Type::OBJECT))
                    )
                ),
                new Response(
                    202,
                    ['Children with discriminator'],
                    new PaginatedOutput(
                        'children_with_discriminator',
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMSDiscriminatorFirstChildObject::class),
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMSDiscriminatorSecondChildObject::class),
                    )
                ),
                new Response(
                    203,
                    ['Parent class with discriminator'],
                    new PaginatedOutput(
                        'parent_with_discriminator',
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMSDiscriminatorParentObject::class),
                    )
                ),
            ],
            Model\Security\Reference::fromString('ApiKey')
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
                new FormInput(
                    new FormDefinition(TestSchemaGeneration\Form\TestType::class),
                    Input::LOCATION_BODY
                ),
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
                new FormInput(
                    new FormDefinition(TestSchemaGeneration\Form\TestType::class),
                    Input::LOCATION_BODY
                ),
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
                new FormInput(
                    new FormDefinition(TestSchemaGeneration\Form\TestFileUpload::class),
                    Input::LOCATION_BODY
                ),
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
                new FormInput(
                    new FormDefinition(TestSchemaGeneration\Form\TestFileUploadOptional::class),
                    Input::LOCATION_BODY
                ),
            ],
            [
                Response::for400WithForm(TestSchemaGeneration\Form\TestFileUploadOptional::class),
            ],
            Model\Security\Reference::fromString('ApiKey')
        ),
    ],
    [
        Input\HeaderInput::withName('X-ALWAYS'),
    ],
    [
        Response::for500(),
    ]
);
