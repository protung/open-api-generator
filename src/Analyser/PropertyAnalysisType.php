<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Analyser;

interface PropertyAnalysisType
{
    public function type(): string;

    public function nullable(): bool;

    /**
     * @return array<mixed>
     */
    public function parameters(): array;
}
