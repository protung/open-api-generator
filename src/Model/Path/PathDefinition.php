<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path;

use Speicher210\OpenApiGenerator\Model\Response;

interface PathDefinition
{
    public function tag() : string;

    public function summary() : string;

    /**
     * @return Input[]
     */
    public function inputs() : array;

    /**
     * @return Response[]
     */
    public function responses() : array;
}
