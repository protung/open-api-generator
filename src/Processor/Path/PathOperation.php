<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor\Path;

use cebe\openapi\spec\Operation;
use function strtolower;

final class PathOperation
{
    private string $operationMethod;

    private string $path;

    private Operation $operation;

    public function __construct(string $operationMethod, string $path, Operation $operation)
    {
        $this->operationMethod = strtolower($operationMethod);
        $this->path            = $path;
        $this->operation       = $operation;
    }

    public function operationMethod() : string
    {
        return $this->operationMethod;
    }

    public function path() : string
    {
        return $this->path;
    }

    public function operation() : Operation
    {
        return $this->operation;
    }

}
