<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;

final class Model
{
    private Definition $definition;

    private Schema $schema;

    private ?Reference $reference;

    public function __construct(Definition $definition, Schema $schema, ?Reference $reference)
    {
        $this->definition = $definition;
        $this->schema     = $schema;
        $this->reference  = $reference;
    }

    public function definition() : Definition
    {
        return $this->definition;
    }

    public function schema() : Schema
    {
        return $this->schema;
    }

    public function setReference(Reference $reference) : void
    {
        $this->reference = $reference;
    }

    public function reference() : ?Reference
    {
        return $this->reference;
    }
}
