<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form;

use Speicher210\OpenApiGenerator\Model\FormDefinition;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

use function array_merge;

final class FormFactory
{
    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function create(FormDefinition $formDefinition, ?string $httpMethod): FormInterface
    {
        return $this->formFactory->create(
            $formDefinition->formClass(),
            null,
            array_merge(
                $formDefinition->formOptions(),
                [
                    'validation_groups' => $formDefinition->validationGroups(),
                    'method' => $httpMethod ?? 'POST', // POST is a default value from Symfony.
                ]
            )
        );
    }
}
