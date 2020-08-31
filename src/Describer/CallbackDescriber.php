<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Callback as SpecCallback;
use Speicher210\OpenApiGenerator\Model\Callback as CallbackModel;
use Speicher210\OpenApiGenerator\Model\Path\PathOperation;

use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

final class CallbackDescriber
{
    private OperationDescriber $operationDescriber;

    public function __construct(OperationDescriber $operationDescriber)
    {
        $this->operationDescriber = $operationDescriber;
    }

    public function describe(CallbackModel $callback): SpecCallback
    {
        $method = $callback->method();
        $path   = $callback->path();

        $callbackPathOperation = new PathOperation(
            $method,
            $callback->url(),
            $this->operationDescriber->describe($method, $path)
        );

        return $this->createCallbackFromPathOperation($callbackPathOperation);
    }

    /**
     * We need to pass the callback data as an array to the constructor of the cebe\openapi\spec\Callback.
     * For this reason we convert the operation into array by wrapping it into json encode and json decode.
     */
    private function createCallbackFromPathOperation(PathOperation $pathOperation): SpecCallback
    {
        return new SpecCallback(
            [
                $pathOperation->path() => [
                    $pathOperation->operationMethod() => json_decode(
                        json_encode($pathOperation->operation()->getSerializableData(), JSON_THROW_ON_ERROR),
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    ),
                ],
            ]
        );
    }
}
