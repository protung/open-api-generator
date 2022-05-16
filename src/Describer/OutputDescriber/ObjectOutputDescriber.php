<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Psl;
use Speicher210\OpenApiGenerator\Describer\ExampleDescriber\ExampleDescriber;
use Speicher210\OpenApiGenerator\Describer\ObjectDescriber;
use Speicher210\OpenApiGenerator\Model\Definition;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\ObjectOutput;

final class ObjectOutputDescriber implements OutputDescriber
{
    private ObjectDescriber $objectDescriber;

    private ExampleDescriber $exampleDescriber;

    public function __construct(ObjectDescriber $objectDescriber, ExampleDescriber $exampleDescriber)
    {
        $this->objectDescriber  = $objectDescriber;
        $this->exampleDescriber = $exampleDescriber;
    }

    public function describe(Output $output): Schema
    {
        $output = Psl\Type\instance_of(ObjectOutput::class)->coerce($output);

        $definition = Definition::fromObjectOutput($output);

        $schema = $this->objectDescriber->describe($definition);

        if ($this->exampleDescriber->supports($output)) {
            $this->exampleDescriber->describe($schema, $output);
        }

        return $schema;
    }

    public function supports(Output $output): bool
    {
        return $output instanceof ObjectOutput;
    }
}
