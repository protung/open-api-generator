<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Protung\OpenApiGenerator\Describer\ExampleDescriber\ExampleDescriber;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\Output\CollectionOutput;
use Psl;

final class CollectionOutputDescriber implements OutputDescriber
{
    private \Protung\OpenApiGenerator\Describer\OutputDescriber $outputDescriber;

    private ExampleDescriber $exampleDescriber;

    public function __construct(
        \Protung\OpenApiGenerator\Describer\OutputDescriber $outputDescriber,
        ExampleDescriber $exampleDescriber
    ) {
        $this->outputDescriber  = $outputDescriber;
        $this->exampleDescriber = $exampleDescriber;
    }

    public function describe(Output $output): Schema
    {
        $output = Psl\Type\instance_of(CollectionOutput::class)->coerce($output);

        $schema = new Schema(['type' => Type::ARRAY, 'items' => $this->outputDescriber->describe($output->output())]);

        if ($this->exampleDescriber->supports($output)) {
            $this->exampleDescriber->describe($schema, $output);
        }

        return $schema;
    }

    public function supports(Output $output): bool
    {
        return $output instanceof CollectionOutput;
    }
}
