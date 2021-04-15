<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\FormErrorOutput;
use Symfony\Component\Form\FormInterface;

use function count;

final class FormErrorOutputDescriber implements OutputDescriber
{
    private FormFactory $formFactory;

    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function describe(Output $output): Schema
    {
        Assert::isInstanceOf($output, FormErrorOutput::class);

        $form = $this->formFactory->create($output->formDefinition(), null);

        return new Schema(
            [
                'type' => Type::OBJECT,
                'properties' => [
                    'type' => ['type' => Type::STRING],
                    'title' => ['type' => Type::STRING],
                    'status' => ['type' => Type::INTEGER],
                    'detail' => ['type' => Type::STRING],
                    'violations' => [
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
    private function describeFormProperties(FormInterface $form): array
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

    public function supports(Output $output): bool
    {
        return $output instanceof FormErrorOutput;
    }
}
