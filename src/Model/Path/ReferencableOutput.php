<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path;

use Protung\OpenApiGenerator\Model\Path\Output\ObjectOutput;

interface ReferencableOutput extends Output
{
    public function output(): ObjectOutput;

    public function referencePath(): string;
}
