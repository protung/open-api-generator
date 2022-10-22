<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Protung\OpenApiGenerator\Describer\Form\FormFactory;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\Output\FormErrorOutput;
use Psl;
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
        $output = Psl\Type\instance_of(FormErrorOutput::class)->coerce($output);

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
                                'required' => $this->extractChildrenNames($form),
                            ],
                        ],
                    ],
                ],
                'required' => [
                    'type',
                    'title',
                    'status',
                    'detail',
                    'violations',
                ],
            ],
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
                'required' => $this->extractChildrenNames($child),
            ];
        }

        return $properties;
    }

    /**
     * @return list<string>
     */
    private function extractChildrenNames(FormInterface $form): array
    {
        return Psl\Vec\map($form, static fn (FormInterface $child): string => $child->getName());
    }

    public function supports(Output $output): bool
    {
        return $output instanceof FormErrorOutput;
    }
}
