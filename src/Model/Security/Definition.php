<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Security;

final class Definition
{
    private const TYPE_HTTP    = 'http';
    private const TYPE_API_KEY = 'apiKey';

    public const IN_HEADER = 'header';
    public const IN_QUERY  = 'query';

    public const SCHEME_HTTP_BASIC  = 'basic';
    public const SCHEME_HTTP_BEARER = 'bearer';

    private string $key;
    private string $type;
    private string|null $in;
    private string|null $description;
    private string|null $name;
    private string|null $scheme;
    private string|null $bearerFormat;

    private function __construct(
        string $key,
        string $type,
        string|null $in,
        string|null $name,
        string|null $description = null,
        string|null $scheme = null,
        string|null $bearerFormat = null,
    ) {
        $this->key          = $key;
        $this->type         = $type;
        $this->in           = $in;
        $this->description  = $description;
        $this->name         = $name;
        $this->scheme       = $scheme;
        $this->bearerFormat = $bearerFormat;
    }

    public static function apiKey(
        string $key,
        string $name,
        string|null $description = null,
        string|null $in = self::IN_HEADER,
    ): self {
        return new self(
            $key,
            self::TYPE_API_KEY,
            $in,
            $name,
            $description,
        );
    }

    public static function basicAuth(string $key, string|null $description = null): self
    {
        return new self(
            $key,
            self::TYPE_HTTP,
            null,
            null,
            $description,
            self::SCHEME_HTTP_BASIC,
        );
    }

    public static function bearerAuth(string $key, string|null $bearerFormat = null, string|null $description = null): self
    {
        return new self(
            $key,
            self::TYPE_HTTP,
            null,
            null,
            $description,
            self::SCHEME_HTTP_BEARER,
            $bearerFormat,
        );
    }

    public function key(): string
    {
        return $this->key;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function in(): string|null
    {
        return $this->in;
    }

    public function description(): string|null
    {
        return $this->description;
    }

    public function name(): string|null
    {
        return $this->name;
    }

    public function scheme(): string|null
    {
        return $this->scheme;
    }

    public function bearerFormat(): string|null
    {
        return $this->bearerFormat;
    }
}
