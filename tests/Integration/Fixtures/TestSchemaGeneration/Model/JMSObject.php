<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model;

final class JMSObject
{
    public string $stringProperty = 'stringProperty';

    public string $stringPropertyWithCustomGroup = 'stringPropertyWithCustomGroup';

    public ?string $nullableStringProperty = null;

    public int $intProperty = 1;

    public float $floatProperty = 1.23;

    public bool $boolProperty = true;

    /** @var string[] */
    public array $arrayProperty = ['test'];

    public ?JMSChildObject $childObjectProperty = null;

    public function getVirtualProperty() : int
    {
        return 123;
    }

    public function getVirtualPropertyWithCustomName() : string
    {
        return 'testing';
    }
}
