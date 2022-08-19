<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS;

final class InlineArrayOfObjects
{
    /** @var ChildObject[] */
    public array $inlineArrayOfChildObjectsProperty = [];
}
