<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\CollectionOutput;

final class CollectionOutputDescriber implements OutputDescriber
{
    private \Speicher210\OpenApiGenerator\Describer\OutputDescriber $outputDescriber;

    public function __construct(\Speicher210\OpenApiGenerator\Describer\OutputDescriber $outputDescriber)
    {
        $this->outputDescriber = $outputDescriber;
    }

    public function describe(Output $output) : Schema
    {
        Assert::isInstanceOf($output, CollectionOutput::class);

        return new Schema(['type' => Type::ARRAY, 'items' => $this->outputDescriber->describe($output->output())]);
    }

    public function supports(Output $output) : bool
    {
        return $output instanceof CollectionOutput;
    }
}
