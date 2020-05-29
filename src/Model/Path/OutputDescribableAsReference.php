<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path;

interface OutputDescribableAsReference extends Output
{
    public function shouldBeDescribedAsReference() : bool;
}
