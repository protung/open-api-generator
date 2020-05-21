<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path;

interface Response
{
    public function statusCode() : int;

    public function description() : string;

    public function output(): ?object;
}
