<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path;

interface Input
{
    public function location(): InputLocation;

    public function isInHeaders(): bool;

    public function isInPath(): bool;

    public function isInQuery(): bool;

    public function isInBody(): bool;
}
