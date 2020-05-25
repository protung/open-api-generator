<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use cebe\openapi\SpecObjectInterface;
use InvalidArgumentException;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Model\Definition;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\CollectionOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\FormErrorOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\ObjectOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\PaginatedOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\ScalarOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\SimpleOutput;
use Symfony\Component\Form\FormInterface;
use function array_fill_keys;
use function array_map;
use function count;
use function get_class;
use function reset;
use function sprintf;

final class OutputDescriber
{
    public const RESPONSE_CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    private JMSModel $jmsModelDescriber;

    private FormFactory $formFactory;

    public function __construct(JMSModel $jmsModelDescriber, FormFactory $formFactory)
    {
        $this->jmsModelDescriber = $jmsModelDescriber;
        $this->formFactory       = $formFactory;
    }

    /**
     * @return Reference|Schema
     */
    public function describe(Output $output) : SpecObjectInterface
    {
        if ($output instanceof CollectionOutput) {
            $schema = $this->describe($output->output());

            return new Schema(['type' => Type::ARRAY, 'items' => $schema]);
        }

        if ($output instanceof ScalarOutput) {
            return $this->describeScalarOutput($output);
        }

        // This handles ErrorResponse as well (ErrorResponse extends SimpleOutput)
        if ($output instanceof SimpleOutput) {
            return $this->describeSimpleOutput($output);
        }

        if ($output instanceof ObjectOutput) {
            return $this->describeObjectOutput($output);
        }

        if ($output instanceof PaginatedOutput) {
            return $this->describePaginatedOutput($output);
        }

        if ($output instanceof FormErrorOutput) {
            return $this->describeFormErrorOutput($output);
        }

        throw new InvalidArgumentException(
            sprintf('Can not handle object to describe of type "%s"', get_class($output))
        );
    }

    private function describeFormErrorOutput(FormErrorOutput $output) : Schema
    {
        $form = $this->formFactory->create($output->formDefinition());

        return new Schema(
            [
                'type' => Type::OBJECT,
                'properties' => [
                    'code' => ['type' => Type::INTEGER],
                    'message' => ['type' => Type::STRING],
                    'errors' => [
                        'type' => Type::OBJECT,
                        'properties' => [
                            'errors' => ['type' => Type::ARRAY, 'items' => ['type' => Type::STRING]],
                            'children' => [
                                'type' => Type::OBJECT,
                                'properties' => $this->describeFormProperties($form),
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @return mixed[]
     */
    private function describeFormProperties(FormInterface $form) : array
    {
        $properties = [];
        foreach ($form as $child) {
            $properties[$child->getName()] = [
                'type' => Type::OBJECT,
                'properties' => [
                    'errors' => ['type' => Type::ARRAY, 'items' => ['type' => Type::STRING]],
                ],
            ];

            if (count($child) <= 0) {
                continue;
            }

            $properties[$child->getName()]['properties']['children'] = [
                'type' => Type::OBJECT,
                'properties' => $this->describeFormProperties($child),
            ];
        }

        return $properties;
    }

    private function describePaginatedOutput(PaginatedOutput $output) : Schema
    {
        return new Schema(
            [
                'properties' => [
                    'page' => new Schema(['type' => Type::INTEGER]),
                    'limit' => new Schema(['type' => Type::INTEGER]),
                    'pages' => new Schema(['type' => Type::INTEGER]),
                    'total' => new Schema(['type' => Type::INTEGER]),
                    '_links' => $this->createLinksSchema(),
                    '_embedded' => $this->createEmbeddedSchema($output),
                ],
            ]
        );
    }

    private function createLinksSchema() : Schema
    {
        return new Schema(
            [
                'properties' => array_fill_keys(
                    ['self', 'first', 'last', 'previous', 'next'],
                    // @todo oneOf schema|null where applicable (last, etc)
                    new Schema(
                        [
                            'properties' => ['href' => new Schema(['type' => Type::STRING])],
                            'type' => Type::OBJECT,
                        ]
                    )
                ),
                'type' => Type::OBJECT,
            ]
        );
    }

    private function createEmbeddedSchema(PaginatedOutput $output) : Schema
    {
        $resourcesSchema = new Schema(['type' => Type::ARRAY]);

        $resources = array_map(
            function (Output $resource) {
                return $this->describe($resource);
            },
            $output->embedded()
        );

        if (count($resources) > 1) {
            $resourcesSchema->items = new Schema(['oneOf' => $resources]);
        } else {
            $resourcesSchema->items = reset($resources);
        }

        return new Schema(
            [
                'type' => Type::OBJECT,
                'properties' => [$output->resourcesKey() => $resourcesSchema],
            ]
        );
    }

    private function describeSimpleOutput(SimpleOutput $output) : Schema
    {
        $properties = [];
        foreach ($output->fields() as $field) {
            $properties[$field->name()] = ['type' => $field->type()];

            if ($field->possibleValues() === null) {
                continue;
            }

            $properties[$field->name()]['enum'] = $field->possibleValues();
        }

        return new Schema(['type' => Type::OBJECT, 'properties' => $properties, 'example' => $output->example()]);
    }

    private function describeScalarOutput(ScalarOutput $output) : Schema
    {
        return new Schema(['type' => $output->type(), 'example' => $output->example()]);
    }

    /**
     * @return Reference|Schema
     */
    private function describeObjectOutput(ObjectOutput $output) : SpecObjectInterface
    {
        return $this->jmsModelDescriber->describe(new Definition($output->className(), $output->serializationGroups()));
    }
}
