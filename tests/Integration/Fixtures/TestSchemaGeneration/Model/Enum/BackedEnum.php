<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\Enum;

final class BackedEnum
{
    public IntegerBackedEnum $integer;

    public StringBackedEnum $string;
}
