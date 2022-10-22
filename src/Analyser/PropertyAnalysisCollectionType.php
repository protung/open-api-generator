<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Analyser;

final class PropertyAnalysisCollectionType implements PropertyAnalysisType
{
    private string $type;

    private bool $nullable;

    private PropertyAnalysisType|null $collectionElementsType;

    private function __construct(string $type, bool $nullable, PropertyAnalysisType|null $collectionElementsType)
    {
        $this->type                   = $type;
        $this->nullable               = $nullable;
        $this->collectionElementsType = $collectionElementsType;
    }

    public static function forCollection(
        string $type,
        bool $nullable,
        PropertyAnalysisType|null $collectionElementsType,
    ): self {
        return new self($type, $nullable, $collectionElementsType);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }

    public function collectionElementsType(): PropertyAnalysisType|null
    {
        return $this->collectionElementsType;
    }

    /**
     * {@inheritDoc}
     */
    public function parameters(): array
    {
        return [];
    }
}
