<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form;

use Protung\OpenApiGenerator\Model\FormDefinition;
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

    /** @return FormInterface<mixed> */
    public function create(FormDefinition $formDefinition, string|null $httpMethod): FormInterface
    {
        return $this->formFactory->create(
            $formDefinition->formClass(),
            null,
            array_merge(
                $formDefinition->formOptions(),
                [
                    'method' => $httpMethod ?? 'POST', // POST is a default value from Symfony.
                ],
            ),
        );
    }
}
