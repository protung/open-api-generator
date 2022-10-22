<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Info;

/**
 * @psalm-immutable
 */
final class Info
{
    private string $title;
    private string|null $description;
    private string|null $apiVersion;

    public function __construct(string $title, string|null $description = null, string|null $apiVersion = null)
    {
        $this->title       = $title;
        $this->description = $description;
        $this->apiVersion  = $apiVersion;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string|null
    {
        return $this->description;
    }

    public function apiVersion(): string|null
    {
        return $this->apiVersion;
    }
}
