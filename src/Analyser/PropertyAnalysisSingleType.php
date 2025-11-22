<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Analyser;

use Override;

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

    public static function forSingleMixedValue(): self
    {
        return new self('mixed', true, []);
    }

    #[Override]
    public function type(): string
    {
        return $this->type;
    }

    #[Override]
    public function nullable(): bool
    {
        return $this->nullable;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function parameters(): array
    {
        return $this->parameters;
    }
}
