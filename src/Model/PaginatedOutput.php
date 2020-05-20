<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

final class PaginatedOutput
{
    private string $resourcesKey;

    /** @var PaginatedOutputResource[] */
    private array $resources;

    public function __construct(string $resourcesKey, PaginatedOutputResource ...$resources)
    {
        $this->resourcesKey = $resourcesKey;
        $this->resources = $resources;
    }

    public function resourcesKey(): string
    {
        return $this->resourcesKey;
    }

    /**
     * @return PaginatedOutputResource[]
     */
    public function resources(): array
    {
        return $this->resources;
    }
}
