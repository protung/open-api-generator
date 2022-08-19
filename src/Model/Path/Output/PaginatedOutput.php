<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Model\Path\Output;

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

    public function example(): mixed
    {
        // @todo implement
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function contentTypes(): array
    {
        return [Output::CONTENT_TYPE_APPLICATION_JSON];
    }
}
