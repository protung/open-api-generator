<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Describer\IOFieldDescriber;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\SimpleOutput;

final class SimpleOutputDescriber implements OutputDescriber
{
    private IOFieldDescriber $ioFieldDescriber;

    public function __construct()
    {
        $this->ioFieldDescriber = new IOFieldDescriber();
    }

    public function describe(Output $output): Schema
    {
        Assert::isInstanceOf($output, SimpleOutput::class);

        $schema          = $this->ioFieldDescriber->describeFields($output->fields());
        $schema->example = $output->example();

        return $schema;
    }

    public function supports(Output $output): bool
    {
        return $output instanceof SimpleOutput;
    }
}
