<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model;

final class JMSInlineObject
{
    public string $inlineObjectStringProperty = 'inlineObjectStringProperty';

    public int $inlineObjectIntProperty = 1;

    public ?JMSChildObject $inlineObjectChildObjectProperty = null;
}
