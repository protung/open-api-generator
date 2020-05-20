<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use Assert\Assertion;

final class PaginatedOutputResource
{
    public const SCALAR_TYPE_INTEGER = 'integer';
    public const SCALAR_TYPE_STRING  = 'string';
    public const SCALAR_TYPE_BOOLEAN = 'boolean';
    public const SCALAR_TYPE_FLOAT   = 'float';

    private string $type;

    public function __construct(string $type)
    {
        $type = $this->normalizeType($type);

        if (! $this->isScalar($type)) {
            Assertion::classExists($type);
        }

        $this->type = $type;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isScalarType(): bool
    {
        return $this->isScalar($this->type);
    }

    private function normalizeType(string $type): string
    {
        switch ($type) {
            case 'int':
                return self::SCALAR_TYPE_INTEGER;
            case 'bool':
                return self::SCALAR_TYPE_BOOLEAN;
            default:
                return $type;
        }
    }

    private function isScalar(string $type): bool
    {
        return \in_array(
            $type,
            [
                self::SCALAR_TYPE_INTEGER,
                self::SCALAR_TYPE_STRING,
                self::SCALAR_TYPE_BOOLEAN,
                self::SCALAR_TYPE_FLOAT,
            ],
            true
        );
    }
}
