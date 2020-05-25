<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model;

final class JMSChildObject
{
    public string $stringProperty = 'test';

    public ?JMSInnerChildObject $innerChildProperty = null;

    public ?JMSChildObject $recursiveProperty = null;
}
