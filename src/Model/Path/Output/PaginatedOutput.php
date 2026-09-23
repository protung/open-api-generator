<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Override;
use Protung\OpenApiGenerator\Model\Path\Output;
use Psl;

final class PaginatedOutput implements Output
{
    private string $resourcesKey;

    /** @var non-empty-list<Output> */
    private array $embedded;

    public function __construct(string $resourcesKey, Output $embedded, Output ...$moreEmbedded)
    {
        $this->resourcesKey = $resourcesKey;
        $this->embedded     = [$embedded, ...Psl\Vec\values($moreEmbedded)];
    }

    public function resourcesKey(): string
    {
        return $this->resourcesKey;
    }

    /**
     * @return non-empty-list<Output>
     */
    public function embedded(): array
    {
        return $this->embedded;
    }

    #[Override]
    public function example(): mixed
    {
        // @todo implement
        return null;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function contentTypes(): array
    {
        return [Output::CONTENT_TYPE_APPLICATION_JSON];
    }
}
