<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use Assert\Assertion;

final class ObjectOutput
{
    private string $className;

    private bool $asCollection;

    public function __construct(string $className, bool $asCollection = false)
    {
        Assertion::classExists($className);

        $this->className    = $className;
        $this->asCollection = $asCollection;
    }

    public function className(): string
    {
        return $this->className;
    }

    public function asCollection(): bool
    {
        return $this->asCollection;
    }
}
