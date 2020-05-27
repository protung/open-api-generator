<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Analyser;

interface PropertyAnalysisType
{
    public function type() : string;

    public function nullable() : bool;
}
