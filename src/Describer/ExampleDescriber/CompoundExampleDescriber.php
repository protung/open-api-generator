<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\ExampleDescriber;

use cebe\openapi\spec\Schema;
use Override;
use Protung\OpenApiGenerator\Model\Path\Output;

final class CompoundExampleDescriber implements ExampleDescriber
{
    /** @var array<ExampleDescriber> */
    private array $exampleDescribers;

    public function __construct(ExampleDescriber ...$exampleDescribers)
    {
        $this->exampleDescribers = $exampleDescribers;
    }

    #[Override]
    public function describe(Schema $schema, Output $output): void
    {
        foreach ($this->exampleDescribers as $exampleDescriber) {
            if ($exampleDescriber->supports($output)) {
                $exampleDescriber->describe($schema, $output);

                return;
            }
        }
    }

    #[Override]
    public function supports(Output $output): bool
    {
        foreach ($this->exampleDescribers as $exampleDescriber) {
            if ($exampleDescriber->supports($output)) {
                return true;
            }
        }

        return false;
    }
}
