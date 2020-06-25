<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Model\Path\Output;

final class FileOutput implements Output
{
    private string $contentType;

    public function __construct(string $contentType)
    {
        $this->contentType = $contentType;
    }

    public static function forPdf(): self
    {
        return new self('application/pdf');
    }

    public function contentType(): string
    {
        return $this->contentType;
    }

    public function example(): void
    {
        // TODO: Implement example() method.
    }
}
