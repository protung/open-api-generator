<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Override;
use Protung\OpenApiGenerator\Describer\ExampleDescriber\ExampleDescriber;
use Protung\OpenApiGenerator\Describer\ObjectDescriber;
use Protung\OpenApiGenerator\Model\Definition;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\Output\ObjectOutput;
use Psl;

final class ObjectOutputDescriber implements OutputDescriber
{
    private ObjectDescriber $objectDescriber;

    private ExampleDescriber $exampleDescriber;

    public function __construct(ObjectDescriber $objectDescriber, ExampleDescriber $exampleDescriber)
    {
        $this->objectDescriber  = $objectDescriber;
        $this->exampleDescriber = $exampleDescriber;
    }

    #[Override]
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

    #[Override]
    public function supports(Output $output): bool
    {
        return $output instanceof ObjectOutput;
    }
}
