<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Analyser;

final class PropertyAnalysisSingleType implements PropertyAnalysisType
{
    private string $type;

    private bool $nullable;

    /** @var array<mixed> */
    private array $parameters;

    /**
     * @param array<mixed> $parameters
     */
    private function __construct(string $type, bool $nullable, array $parameters)
    {
        $this->type       = $type;
        $this->nullable   = $nullable;
        $this->parameters = $parameters;
    }

    /**
     * @param array<mixed> $parameters
     */
    public static function forSingleValue(string $type, bool $nullable, array $parameters): self
    {
        return new self($type, $nullable, $parameters);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }

    /**
     * {@inheritDoc}
     */
    public function parameters(): array
    {
        return $this->parameters;
    }
}
