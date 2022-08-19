<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Processor\Path;

use Protung\OpenApiGenerator\Model\Path\Path;
use Protung\OpenApiGenerator\Model\Path\PathOperation;

interface PathProcessor
{
    /**
     * @return PathOperation[]
     */
    public function process(Path $path): array;

    public function canProcess(Path $path): bool;
}
