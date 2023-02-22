<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path;

enum InputLocation: string
{
    case Header = 'header';
    case Path   = 'path';
    case Query  = 'query';
    case Body   = 'body';
}
