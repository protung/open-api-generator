<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\Enum;

enum IntegerBackedEnum: int
{
    case ONE   = 1;
    case TWO   = 2;
    case THREE = 3;
}
