<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Info;

final class Info
{
    private string $title;
    private ?string $description;
    private ?string $apiVersion;

    public function __construct(string $title, ?string $description = null, ?string $apiVersion = null)
    {
        $this->title       = $title;
        $this->description = $description;
        $this->apiVersion  = $apiVersion;
    }

    public function title() : string
    {
        return $this->title;
    }

    public function description() : ?string
    {
        return $this->description;
    }

    public function apiVersion() : ?string
    {
        return $this->apiVersion;
    }
}
