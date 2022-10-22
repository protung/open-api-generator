<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS;

final class ChildObject
{
    public string $stringProperty = 'test';

    public string $stringPropertyWithCustomGroup = 'stringPropertyWithCustomGroup';

    public InnerChildObject|null $innerChildProperty = null;

    public ChildObject|null $recursiveProperty = null;
}
