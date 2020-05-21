<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use cebe\openapi\SpecObjectInterface;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Model\FormDefinition;
use Speicher210\OpenApiGenerator\Model\Path\Output\CollectionOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\ErrorResponse;
use Speicher210\OpenApiGenerator\Model\Path\Output\ObjectOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\PaginatedOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\ScalarOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\SimpleOutput;
use Symfony\Component\Form\FormInterface;

final class Output
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
     * @param string[]|null $serializationGroups
     *
     * @return Reference|Schema
     */
    public function describe(object $output, ?array $serializationGroups) : SpecObjectInterface
    {
        if ($output instanceof CollectionOutput) {
            $schema = $this->describe($output->output(), $serializationGroups);

            return new Schema(['type' => Type::ARRAY, 'items' => $schema]);
        }

        if ($output instanceof ScalarOutput) {
            return $this->describeScalarOutput($output);
        }

        if ($output instanceof SimpleOutput) {
            return $this->describeSimpleOutput($output);
        }

        if ($output instanceof ObjectOutput) {
            return $this->describeObjectOutput($output, $serializationGroups);
        }

        if ($output instanceof PaginatedOutput) {
            return $this->describePaginatedOutput($output, $serializationGroups);
        }

        if ($output instanceof ErrorResponse) {
            return $this->describe($output->output(), $serializationGroups);
        }

        if ($output instanceof FormDefinition) {
            return $this->describeFormDefinition($output);
        }

        throw new \InvalidArgumentException(
            \sprintf('Can not handle object to describe of type "%s"', \get_class($output))
        );
    }

    private function describeFormDefinition(FormDefinition $output) : Schema
    {
        $form = $this->formFactory->create($output);

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

            if (\count($child) <= 0) {
                continue;
            }

            $properties[$child->getName()]['properties']['children'] = [
                'type' => Type::OBJECT,
                'properties' => $this->describeFormProperties($child),
            ];
        }

        return $properties;
    }

    /**
     * @param string[]|null $serializationGroups
     */
    private function describePaginatedOutput(PaginatedOutput $output, ?array $serializationGroups) : Schema
    {
        $properties['page']      = new Schema(['type' => Type::INTEGER]);
        $properties['limit']     = new Schema(['type' => Type::INTEGER]);
        $properties['pages']     = new Schema(['type' => Type::INTEGER]);
        $properties['total']     = new Schema(['type' => Type::INTEGER]);
        $properties['_links']    = $this->createLinksSchema();
        $properties['_embedded'] = $this->createEmbeddedSchema($output, $serializationGroups);

        return new Schema(['properties' => $properties]);
    }

    private function createLinksSchema() : Schema
    {
        return new Schema(
            [
                'properties' => \array_fill_keys(
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

    /**
     * @param string[]|null $serializationGroups
     */
    private function createEmbeddedSchema(PaginatedOutput $output, ?array $serializationGroups) : Schema
    {
        $resourcesSchema = new Schema(['type' => Type::ARRAY]);

        $resources = \array_values(
            \array_map(
                function (PaginatedOutputResource $resource) use ($serializationGroups) {
                    if ($resource->isScalarType()) {
                        $resourceSchema = new Schema(['type' => $resource->type()]);
                    } else {
                        $resourceSchema = $this->jmsModelDescriber->describe($resource->type(), $serializationGroups);
                    }

                    return $resourceSchema;
                },
                $output->resources()
            )
        );

        if (\count($resources) > 1) {
            $resourcesSchema->items = new Schema(['oneOf' => $resources]);
        } else {
            $resourcesSchema->items = \reset($resources);
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
        }
        return new Schema(['type' => Type::OBJECT, 'properties' => $properties, 'example' => $output->example()]);
    }

    private function describeScalarOutput(ScalarOutput $output) : Schema
    {
        return new Schema(['type' => $output->type(), 'example' => $output->example()]);
    }

    /**
     * @param string[]|null $serializationGroups
     *
     * @return Reference|Schema
     */
    private function describeObjectOutput(ObjectOutput $output, ?array $serializationGroups) : SpecObjectInterface
    {
        return $this->jmsModelDescriber->describe($output->className(), $serializationGroups);
    }
}
