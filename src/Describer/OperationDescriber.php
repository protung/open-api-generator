<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Callback as ModelCallback;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use Speicher210\OpenApiGenerator\Model\Path\Path;
use Speicher210\OpenApiGenerator\Model\Response as ModelResponse;

use function array_filter;

use const ARRAY_FILTER_USE_BOTH;

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
            array_filter(
                [
                    'summary' => $path->summary(),
                    'description' => $path->description(),
                    'tags' => [$path->tag()],
                    'deprecated' => $path->isDeprecated(),
                    'security' => $path->security()->references(),
                    'parameters' => [],
                    'responses' => new Responses([]),
                ],
                /**
                 * @param mixed $value
                 */
                static function ($value, string $key): bool {
                    if ($key === 'deprecated' && $value === false) {
                        return false;
                    }

                    return $value !== null;
                },
                ARRAY_FILTER_USE_BOTH
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
        Assert::isInstanceOf($operation->responses, Responses::class);

        foreach ($responsesConfig as $response) {
            $responseData = [
                'description' => $response->description(),
            ];

            foreach ($response->outputs() as $output) {
                $outputSchema = $this->outputDescriber->describe($output);
                foreach ($output->contentTypes() as $contentType) {
                    $responseData['content'][$contentType] = ['schema' => $outputSchema];
                }
            }

            $operation->responses->addResponse(
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
