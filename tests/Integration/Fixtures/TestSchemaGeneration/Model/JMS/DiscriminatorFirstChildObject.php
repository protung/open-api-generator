<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS;

final class DiscriminatorFirstChildObject extends DiscriminatorParentObject
{
    public string $propertyInFirstChild = 'propertyInFirstChild';

    public int $childPropertyWithDifferentType = 1;
}
