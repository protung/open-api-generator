Open Api Generator
==================

[![Build](https://github.com/protung/open-api-generator/workflows/Build/badge.svg)](https://github.com/protung/open-api-generator/actions?query=workflow%3ABuild+branch%3Amain)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)

## Installation

Require using composer:

```shell
$ composer require protung/open-api-generator
```

## Usage

Describe each endpoint by its Symfony route name, and list what it accepts and returns. The generator reads the rest
from Symfony and JMS Serializer: path parameters from the route, request bodies from forms and payload classes,
requirements from validation constraints, and response schemas from the serialization metadata.

```php
use Protung\OpenApiGenerator\GeneratorFactory;
use Protung\OpenApiGenerator\Model\Info\Info;
use Protung\OpenApiGenerator\Model\Path\Input\SymfonyMappedPayloadInput;
use Protung\OpenApiGenerator\Model\Path\Output\ObjectOutput;
use Protung\OpenApiGenerator\Model\Path\Output\SymfonyPayloadValidationProblemOutput;
use Protung\OpenApiGenerator\Model\Response;
use Protung\OpenApiGenerator\Model\Security\Definition;
use Protung\OpenApiGenerator\Model\Security\Reference;
use Protung\OpenApiGenerator\Model\Specification;
use Protung\OpenApiGenerator\Processor\Path\Symfony\SymfonyRoutePath;

$generator = GeneratorFactory::create(
    apiVersion: '1.0.0',
    router: $router,                   // the "router" service
    formFactory: $formFactory,         // the "form.factory" service
    metadataFactory: $metadataFactory, // the "jms_serializer.metadata_factory" service
    validator: $validator,             // the "validator" service
    jmsSerializer: $serializer,        // the "jms_serializer" service
);

$specification = new Specification(
    new Info('Books API'),
    [Definition::bearerAuth('bearer')],
    [
        new SymfonyRoutePath(
            routeName: 'api_book_get',
            tag: 'Books',
            summary: 'Get a book',
            description: null,
            input: [],
            responses: [
                Response::for200(ObjectOutput::forClass(Book::class)),
                Response::for404(),
            ],
            security: Reference::fromString('bearer'),
        ),
        new SymfonyRoutePath(
            routeName: 'api_book_create',
            tag: 'Books',
            summary: 'Create a book',
            description: null,
            input: [SymfonyMappedPayloadInput::forClass(CreateBookRequest::class)],
            responses: [
                Response::for201(ObjectOutput::forClass(Book::class)),
                Response::for422(SymfonyPayloadValidationProblemOutput::forClass(CreateBookRequest::class)),
            ],
            security: Reference::fromString('bearer'),
        ),
    ],
);

$openApi = $generator->generate($specification);

echo json_encode($openApi->getSerializableData(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
```

## License

This package is released under the [MIT license](LICENSE.md).
