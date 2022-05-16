<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\InputDescriber;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Schema;
use Psl;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Describer\FormDescriber;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use Symfony\Component\Form\FormInterface;

use function array_key_exists;
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
        $input = Psl\Type\instance_of(Input\FormInput::class)->coerce($input);

        $form = $this->formFactory->create($input->formDefinition(), $httpMethod);

        if ($input->isInQuery()) {
            $operation->parameters = array_merge($operation->parameters, $this->queryDescriber->describe($form));
        } elseif ($input->isInBody()) {
            $this->describeRequestBody($operation, $form);
        }
    }

    public function supports(Input $input): bool
    {
        return $input instanceof Input\FormInput;
    }

    private function describeRequestBody(Operation $operation, FormInterface $form): void
    {
        $mediaTypes = $this->bodyDescriber->describe($form);

        if ($operation->requestBody === null) {
            $operation->requestBody = new RequestBody(
                [
                    'required' => true,
                    'content' => $mediaTypes,
                ]
            );

            return;
        }

        $requestBody = Psl\Type\instance_of(RequestBody::class)->coerce($operation->requestBody);

        $requestBody->content = $this->mergeRequestBodyContent(
            $requestBody->content,
            $mediaTypes
        );
    }

    /**
     * @param array<string,MediaType> $requestBodyContent
     * @param array<string,MediaType> $newMediaTypes
     *
     * @return array<string,MediaType>
     */
    private function mergeRequestBodyContent(array $requestBodyContent, array $newMediaTypes): array
    {
        foreach ($newMediaTypes as $contentType => $newMediaType) {
            if (! array_key_exists($contentType, $requestBodyContent)) {
                $requestBodyContent[$contentType] = $newMediaType;

                continue;
            }

            $existingMediaTypeSchema = Psl\Type\instance_of(Schema::class)->coerce($requestBodyContent[$contentType]->schema);

            if ($existingMediaTypeSchema->oneOf === null) {
                $mediaTypes = [
                    $existingMediaTypeSchema,
                    $newMediaType->schema,
                ];
            } else {
                Assert::minCount($existingMediaTypeSchema->oneOf, 1);
                $mediaTypes = array_merge(
                    $existingMediaTypeSchema->oneOf,
                    [$newMediaType->schema]
                );
            }

            $requestBodyContent[$contentType]->schema = new Schema(['oneOf' => $mediaTypes]);
        }

        return $requestBodyContent;
    }
}
