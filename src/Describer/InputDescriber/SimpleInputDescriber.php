<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\InputDescriber;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Describer\IOFieldDescriber;
use Protung\OpenApiGenerator\Model\Path\Input;
use Psl;

use function array_merge;

final class SimpleInputDescriber implements InputDescriber
{
    private const CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    private IOFieldDescriber $ioFieldDescriber;

    public function __construct()
    {
        $this->ioFieldDescriber = new IOFieldDescriber();
    }

    public function describe(Input $input, Operation $operation, string $httpMethod): void
    {
        $input = Psl\Type\instance_of(Input\SimpleInput::class)->coerce($input);

        if ($input->isInBody()) {
            Assert::notSame($httpMethod, 'GET', 'Body input is not allowed in GET requests.');

            $operation->requestBody = new RequestBody(
                [
                    'required' => true,
                    'content' => [
                        self::CONTENT_TYPE_APPLICATION_JSON => new MediaType(
                            ['schema' => $this->ioFieldDescriber->describeFields($input->fields())],
                        ),
                    ],
                ],
            );
        } else {
            $parameters = [];
            // @todo make use of IOFieldDescriber
            foreach ($input->fields() as $field) {
                $parameter           = new Parameter(['name' => $field->name(), 'in' => $input->location()->value]);
                $parameter->required = $input->isInPath();

                $parameterSchema = new Schema(
                    [
                        'type' => $field->type(),
                    ],
                );
                $possibleValues  = $field->possibleValues();
                $fieldPattern    = $field->pattern();
                if ($possibleValues !== null) {
                    $parameterSchema->enum = $possibleValues;
                } elseif ($fieldPattern !== null) {
                    $parameterSchema->pattern = $fieldPattern;
                }

                if ($field->example() !== null) {
                    $parameterSchema->example = $field->example();
                }

                $parameter->schema = $parameterSchema;
                $parameters[]      = $parameter;
            }

            $operation->parameters = array_merge($operation->parameters, $parameters);
        }
    }

    public function supports(Input $input): bool
    {
        return $input instanceof Input\SimpleInput;
    }
}
