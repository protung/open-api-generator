<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Describer\ExampleDescriber\ExampleDescriber;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\CollectionOutput;

final class CollectionOutputDescriber implements OutputDescriber
{
    private \Speicher210\OpenApiGenerator\Describer\OutputDescriber $outputDescriber;
    private ExampleDescriber $exampleDescriber;

    public function __construct(
        \Speicher210\OpenApiGenerator\Describer\OutputDescriber $outputDescriber,
        ExampleDescriber $exampleDescriber
    ) {
        $this->outputDescriber  = $outputDescriber;
        $this->exampleDescriber = $exampleDescriber;
    }

    public function describe(Output $output): Schema
    {
        Assert::isInstanceOf($output, CollectionOutput::class);

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
