<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Protung\OpenApiGenerator\Model\Path\Output;

final class FileOutput implements Output
{
    private string $contentType;

    public function __construct(string $contentType)
    {
        $this->contentType = $contentType;
    }

    public static function forHtml(): self
    {
        return new self('text/html');
    }

    public static function forPlainText(): self
    {
        return new self('text/plain');
    }

    public static function forJpeg(): self
    {
        return new self('image/jpeg');
    }

    public static function forPng(): self
    {
        return new self('image/png');
    }

    public static function forPdf(): self
    {
        return new self('application/pdf');
    }

    public static function forZip(): self
    {
        return new self('application/zip');
    }

    /**
     * {@inheritDoc}
     */
    public function contentTypes(): array
    {
        return [$this->contentType];
    }

    public function example(): mixed
    {
        // @todo implement
        return null;
    }
}
