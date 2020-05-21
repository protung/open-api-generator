<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Assert\Assertion;
use Speicher210\OpenApiGenerator\Model\Path\Output;

final class ObjectOutput implements Output
{
    private string $className;

    public function __construct(string $className)
    {
        Assertion::classExists($className);

        $this->className = $className;
    }

    public function className() : string
    {
        return $this->className;
    }

    public function example()
    {
        // TODO: Implement example() method.
    }
}
