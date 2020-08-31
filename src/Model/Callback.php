<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

final class Callback
{
    private string $eventName;

    private string $url;

    private string $method;

    private Callback\Path $path;

    public function __construct(string $eventName, string $url, string $method, Callback\Path $path)
    {
        $this->eventName = $eventName;
        $this->url       = $url;
        $this->method    = $method;
        $this->path      = $path;
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): Callback\Path
    {
        return $this->path;
    }
}
