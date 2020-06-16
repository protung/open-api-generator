<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\InputDescriber;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\RequestBody;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Describer\FormDescriber;
use Speicher210\OpenApiGenerator\Model\Path\Input;

use function array_merge;

final class FormInputDescriber implements InputDescriber
{
    private FormInputDescriber\Query $queryDescriber;

    private FormInputDescriber\Body $bodyDescriber;

    private FormFactory $formFactory;

    public function __construct(FormDescriber $formDescriber, FormFactory $formFactory)
    {
        $this->queryDescriber = new FormInputDescriber\Query($formDescriber);
        $this->bodyDescriber  = new FormInputDescriber\Body($formDescriber);

        $this->formFactory = $formFactory;
    }

    public function describe(Input $input, Operation $operation, string $httpMethod): void
    {
        Assert::isInstanceOf($input, Input\FormInput::class);

        $form = $this->formFactory->create($input->formDefinition());

        if ($form->count() === 0) {
            return;
        }

        if ($input->isInQuery()) {
            $operation->parameters = array_merge($operation->parameters, $this->queryDescriber->describe($form));
        } elseif ($input->isInBody()) {
            $operation->requestBody = new RequestBody(
                [
                    'required' => true,
                    'content' => $this->bodyDescriber->describe($form, $httpMethod),
                ]
            );
        }
    }

    public function supports(Input $input): bool
    {
        return $input instanceof Input\FormInput;
    }
}
