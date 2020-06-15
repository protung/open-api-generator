<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\ExampleDescriber;

use cebe\openapi\spec\Schema;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\Output;

final class CollectionExampleDescriber implements ExampleDescriber
{
    /** @var array<ExampleDescriber>  */
    private array $exampleDescribers;

    public function __construct(ExampleDescriber ...$exampleDescribers)
    {
        $this->exampleDescribers = $exampleDescribers;
    }

    public function describe(Schema $schema, Output $output) : void
    {
        Assert::isInstanceOf($output, Output\CollectionOutput::class);

        $innerOutput = $output->output();

        $exampleSchema = new Schema(['example' => null]);
        foreach ($this->exampleDescribers as $exampleDescriber) {
            if (! $exampleDescriber->supports($innerOutput)) {
                continue;
            }

            $exampleDescriber->describe($exampleSchema, $innerOutput);
        }

        if ($exampleSchema->example === null) {
            return;
        }

        $schema->example = [$exampleSchema->example];
    }

    public function supports(Output $output) : bool
    {
        return $output instanceof Output\CollectionOutput;
    }
}
