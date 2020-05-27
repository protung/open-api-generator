<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model;

final class JMSDiscriminatorSecondChildObject extends JMSDiscriminatorParentObject
{
    public string $propertyInSecondChild = 'propertyInSecondChild';

    public bool $childPropertyWithDifferentType = true;
}
