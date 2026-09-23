<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\JMS;

final class RecursiveObject
{
    public string $name = 'name';

    public RecursiveObject|null $parent = null;

    /** @var RecursiveObject[] */
    public array $children = [];
}
