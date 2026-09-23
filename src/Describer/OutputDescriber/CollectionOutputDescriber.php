<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Override;
use Protung\OpenApiGenerator\Describer\ExampleDescriber\ExampleDescriber;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\Output\CollectionOutput;
use Psl;

final class CollectionOutputDescriber implements OutputDescriber
{
    private ExampleDescriber $exampleDescriber;

    public function __construct(ExampleDescriber $exampleDescriber)
    {
        $this->exampleDescriber = $exampleDescriber;
    }

    #[Override]
    public function describe(Output $output, \Protung\OpenApiGenerator\Describer\OutputDescriber $outputDescriber): Schema
    {
        $output = Psl\Type\instance_of(CollectionOutput::class)->coerce($output);

        $schema = new Schema(['type' => Type::ARRAY, 'items' => $outputDescriber->describe($output->output())]);

        if ($this->exampleDescriber->supports($output)) {
            $this->exampleDescriber->describe($schema, $output);
        }

        return $schema;
    }

    #[Override]
    public function supports(Output $output): bool
    {
        return $output instanceof CollectionOutput;
    }
}
