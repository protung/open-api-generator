<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\ExampleDescriber;

use cebe\openapi\spec\Schema;
use Override;
use Protung\OpenApiGenerator\Model\Path\Output;
use Psl;

final class CollectionExampleDescriber implements ExampleDescriber
{
    /** @var array<ExampleDescriber>  */
    private array $exampleDescribers;

    public function __construct(ExampleDescriber ...$exampleDescribers)
    {
        $this->exampleDescribers = $exampleDescribers;
    }

    #[Override]
    public function describe(Schema $schema, Output $output): void
    {
        $output = Psl\Type\instance_of(Output\CollectionOutput::class)->coerce($output);

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

    #[Override]
    public function supports(Output $output): bool
    {
        return $output instanceof Output\CollectionOutput;
    }
}
