<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use InvalidArgumentException;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use function array_merge;
use function get_class;
use function sprintf;

final class InputDescriber
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

    public function describe(Operation $operation, Input $input, ?string $httpMethod = null) : ?SpecObjectInterface
    {
        if ($input instanceof Input\FormInput) {
            $form = $this->formFactory->create($input->formDefinition());

            if ($form->count() === 0) {
                return null;
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
        } elseif ($input instanceof Input\SimpleInput) {
            $parameters = [];
            foreach ($input->fields() as $field) {
                $parameter           = new Parameter(['name' => $field->name(), 'in' => $input->location()]);
                $parameter->required = $input->isInPath();

                $parameterSchema = new Schema(
                    [
                        'type' => $field->type(),
                    ]
                );
                if ($field->possibleValues() !== null) {
                    $parameterSchema->enum = $field->possibleValues();
                } elseif ($field->pattern() !== null) {
                    $parameterSchema->pattern = $field->pattern();
                }

                $parameter->schema = $parameterSchema;
                $parameters[]      = $parameter;
            }

            $operation->parameters = array_merge($operation->parameters, $parameters);
        } else {
            throw new InvalidArgumentException(
                sprintf('Can not handle object to describe of type "%s"', get_class($input))
            );
        }

        return null;
    }
}
