<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path;

use Speicher210\OpenApiGenerator\Model\Path\Output\ObjectOutput;

interface ReferencableOutput extends Output
{
    public function output() : ObjectOutput;
}
