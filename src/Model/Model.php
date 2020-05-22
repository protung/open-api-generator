<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use cebe\openapi\spec\Schema;

final class Model
{
    private Definition $definition;

    private Schema $schema;

    public function __construct(Definition $definition, Schema $schema)
    {
        $this->definition = $definition;
        $this->schema     = $schema;
    }

    public function definition() : Definition
    {
        return $this->definition;
    }

    public function schema() : Schema
    {
        return $this->schema;
    }
}
