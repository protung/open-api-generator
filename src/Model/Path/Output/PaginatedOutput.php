<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\Output;

final class PaginatedOutput implements Output
{
    private string $resourcesKey;

    /** @var Output[] */
    private array $embedded;

    public function __construct(string $resourcesKey, Output ...$embedded)
    {
        Assert::minCount($embedded, 1);

        $this->resourcesKey = $resourcesKey;
        $this->embedded     = $embedded;
    }

    public function resourcesKey(): string
    {
        return $this->resourcesKey;
    }

    /**
     * @return Output[]
     */
    public function embedded(): array
    {
        return $this->embedded;
    }

    public function example(): void
    {
        // TODO: Implement example() method.
    }

    /**
     * {@inheritDoc}
     */
    public function contentTypes(): array
    {
        return [Output::CONTENT_TYPE_APPLICATION_JSON];
    }
}
