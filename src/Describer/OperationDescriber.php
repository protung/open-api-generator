<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use Psl;
use Speicher210\OpenApiGenerator\Model\Callback as ModelCallback;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use Speicher210\OpenApiGenerator\Model\Path\Path;
use Speicher210\OpenApiGenerator\Model\Response as ModelResponse;

use function count;

final class OperationDescriber
{
    private InputDescriber $inputDescriber;

    private OutputDescriber $outputDescriber;

    private CallbackDescriber $callbackDescriber;

    public function __construct(InputDescriber $inputDescriber, OutputDescriber $outputDescriber)
    {
        $this->inputDescriber    = $inputDescriber;
        $this->outputDescriber   = $outputDescriber;
        $this->callbackDescriber = new CallbackDescriber($this);
    }

    public function describe(string $method, Path $path): Operation
    {
        $operation = new Operation(
            Psl\Dict\filter_with_key(
                [
                    'summary' => $path->summary(),
                    'description' => $path->description(),
                    'tags' => [$path->tag()],
                    'deprecated' => $path->isDeprecated(),
                    'security' => $path->security()->references(),
                    'parameters' => [],
                    'responses' => new Responses([]),
                ],
                static function (string $key, mixed $value): bool {
                    if ($key === 'deprecated' && $value === false) {
                        return false;
                    }

                    return $value !== null;
                }
            )
        );

        $this->describeInputs(
            $operation,
            $method,
            ...$path->input()
        );
        $this->describeResponses(
            $operation,
            ...$path->responses(),
        );

        $callbacks = $path->callbacks();
        if ($callbacks !== []) {
            $this->describeCallbacks(
                $operation,
                ...$callbacks
            );
        }

        return $operation;
    }

    private function describeInputs(Operation $operation, string $httpMethod, Input ...$inputs): void
    {
        foreach ($inputs as $input) {
            $this->inputDescriber->describe($operation, $input, $httpMethod);
        }
    }

    private function describeResponses(Operation $operation, ModelResponse ...$responsesConfig): void
    {
        $responses = Psl\Type\instance_of(Responses::class)->coerce($operation->responses);

        foreach ($responsesConfig as $response) {
            $responseData = [
                'description' => $response->description(),
            ];

            $responseContentOutputs = [];
            foreach ($response->outputs() as $output) {
                $outputSchema = $this->outputDescriber->describe($output);
                foreach ($output->contentTypes() as $contentType) {
                    $responseContentOutputs[$contentType][] = $outputSchema;
                }
            }

            foreach ($responseContentOutputs as $contentType => $outputs) {
                if (count($outputs) > 1) {
                    $responseData['content'][$contentType] = ['schema' => ['oneOf' => $outputs]];
                } else {
                    $responseData['content'][$contentType] = ['schema' => $outputs[0]];
                }
            }

            $responses->addResponse(
                (string) $response->statusCode(),
                new Response($responseData)
            );
        }
    }

    private function describeCallbacks(Operation $operation, ModelCallback ...$callbackConfigs): void
    {
        $callbacks = [];
        foreach ($callbackConfigs as $callbackConfig) {
            $callbacks[$callbackConfig->eventName()] = $this->callbackDescriber->describe($callbackConfig);
        }

        $operation->callbacks = $callbacks;
    }
}
