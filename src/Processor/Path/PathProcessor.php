<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor\Path;

interface PathProcessor
{
    /**
     * @return PathOperation[]
     */
    public function process(Path $path) : array;
}
