<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\InputDescriber;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\RequestBody;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Describer\Query;
use Speicher210\OpenApiGenerator\Describer\RequestBodyContent;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use function array_merge;

final class FormInputDescriber implements InputDescriber
{
    private Query $queryDescriber;

    private RequestBodyContent $requestBodyContentDescriber;

    private FormFactory $formFactory;

    public function __construct(
        Query $queryDescriber,
        RequestBodyContent $requestBodyContentDescriber,
        FormFactory $formFactory
    ) {
        $this->queryDescriber              = $queryDescriber;
        $this->requestBodyContentDescriber = $requestBodyContentDescriber;
        $this->formFactory                 = $formFactory;
    }

    public function describe(Input $input, Operation $operation, string $httpMethod) : void
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
                    'content' => $this->requestBodyContentDescriber->describe($form, $httpMethod),
                ]
            );
        }
    }

    public function supports(Input $input) : bool
    {
        return $input instanceof Input\FormInput;
    }
}
