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
                new Response(204, [], (new Model\Path\Output\ScalarOutput(Type::BOOLEAN))->withExample(false)),
                new Response(
                    205,
                    [],
                    new Model\Path\Output\SimpleOutput(
                        IOField::integerField('myInt'),
                        IOField::stringField('myString'),
                        new IOField('myChoice', Type::NUMBER, null, [1, 2, 3]),
                    )
                ),
                new Response(
                    206,
                    [],
                    new Model\Path\Output\CollectionOutput(new Model\Path\Output\ScalarOutput(Type::STRING))
                ),
                new Response(207, [], ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ComplexObject::class)),
                new Response(
                    208,
                    [],
                    new Model\Path\Output\CollectionOutput(
                        ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\ComplexObject::class)
                    )
                ),
                new Response(
                    209,
                    [],
                    ObjectOutput::forClass(TestSchemaGeneration\Model\JMS\InlineArrayOfObjects::class)
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
                Response::for400(new RFC7807ErrorOutput(400, 'something custom'), ['Custom message 400']),
                Response::for401(),
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
                        new Model\Path\Output\SimpleOutput(new Model\Path\IOField('someField', Model\Type::OBJECT))
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
    ],
    [
        Input\HeaderInput::withName('X-ALWAYS'),
    ],
    [
        Response::for500(),
    ]
);
