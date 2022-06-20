<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\Enum;

enum StringBackedEnum: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
}
