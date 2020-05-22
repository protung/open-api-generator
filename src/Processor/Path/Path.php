<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor\Path;

use Speicher210\OpenApiGenerator\Model\Path\Input;
use Speicher210\OpenApiGenerator\Model\Response;
use Speicher210\OpenApiGenerator\Model\Security\Reference;

interface Path
{
    public function tag() : string;

    public function summary() : string;

    public function description() : ?string;

    /**
     * @return Input[]
     */
    public function input() : array;

    /**
     * @return Response[]
     */
    public function responses() : array;

    public function security() : Reference;

    public function isDeprecated() : bool;
}
