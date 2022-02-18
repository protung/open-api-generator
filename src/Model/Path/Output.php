<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path;

interface Output
{
    public const CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    public const CONTENT_TYPE_APPLICATION_PROBLEM_JSON = 'application/problem+json';

    public const CONTENT_TYPE_TEXT_PLAIN = 'text/plain';

    public function example(): mixed;

    /**
     * @return string[]
     */
    public function contentTypes(): array;
}
