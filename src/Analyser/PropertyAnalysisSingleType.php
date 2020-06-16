<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Analyser;

final class PropertyAnalysisSingleType implements PropertyAnalysisType
{
    private string $type;

    private bool $nullable;

    private function __construct(string $type, bool $nullable)
    {
        $this->type     = $type;
        $this->nullable = $nullable;
    }

    public static function forSingleValue(string $type, bool $nullable): self
    {
        return new self($type, $nullable);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }
}
