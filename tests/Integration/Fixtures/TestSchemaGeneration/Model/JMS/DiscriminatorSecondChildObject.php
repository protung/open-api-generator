<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS;

final class DiscriminatorSecondChildObject extends DiscriminatorParentObject
{
    public string $propertyInSecondChild = 'propertyInSecondChild';

    public bool $childPropertyWithDifferentType = true;
}
