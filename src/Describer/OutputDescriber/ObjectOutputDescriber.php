<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Describer\ObjectDescriber;
use Speicher210\OpenApiGenerator\Model\Definition;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\ObjectOutput;

final class ObjectOutputDescriber implements OutputDescriber
{
    private ObjectDescriber $objectDescriber;

    public function __construct(ObjectDescriber $objectDescriber)
    {
        $this->objectDescriber = $objectDescriber;
    }

    public function describe(Output $output) : Schema
    {
        Assert::isInstanceOf($output, ObjectOutput::class);

        $definition = new Definition($output->className(), $output->serializationGroups());

        return $this->objectDescriber->describe($definition);
    }

    public function supports(Output $output) : bool
    {
        return $output instanceof ObjectOutput;
    }
}
