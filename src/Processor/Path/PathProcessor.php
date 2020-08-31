<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor\Path;

use Speicher210\OpenApiGenerator\Model\Path\Path;
use Speicher210\OpenApiGenerator\Model\Path\PathOperation;

interface PathProcessor
{
    /**
     * @return PathOperation[]
     */
    public function process(Path $path): array;

    public function canProcess(Path $path): bool;
}
