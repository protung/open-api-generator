<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS;

final class InlineObject
{
    public string $inlineObjectStringProperty = 'inlineObjectStringProperty';

    public int $inlineObjectIntProperty = 1;

    public ChildObject|null $inlineObjectChildObjectProperty = null;
}
