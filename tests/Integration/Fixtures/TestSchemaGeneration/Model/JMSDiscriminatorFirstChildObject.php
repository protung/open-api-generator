<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model;

final class JMSDiscriminatorFirstChildObject extends JMSDiscriminatorParentObject
{
    public string $propertyInFirstChild = 'propertyInFirstChild';

    public int $childPropertyWithDifferentType = 1;
}
