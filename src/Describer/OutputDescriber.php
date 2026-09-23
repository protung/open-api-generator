<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use InvalidArgumentException;
use Protung\OpenApiGenerator\Model\Definition;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\ReferencableOutput;
use Psl;

final class OutputDescriber
{
    private ObjectDescriber $objectDescriber;

    /** @var array<OutputDescriber\OutputDescriber> */
    private array $outputDescribers;

    /**
     * @param ObjectDescriber $objectDescriber Describes the outputs referenced by a ReferencableOutput.
     */
    public function __construct(ObjectDescriber $objectDescriber, OutputDescriber\OutputDescriber ...$outputDescribers)
    {
        $this->objectDescriber  = $objectDescriber;
        $this->outputDescribers = $outputDescribers;
    }

    /**
     * @return Reference|Schema
     */
    public function describe(Output $output): SpecObjectInterface
    {
        if ($output instanceof ReferencableOutput) {
            $definition = Definition::fromObjectOutput($output->output());

            return $this->objectDescriber->describeAsReference($definition, $output->referencePath());
        }

        foreach ($this->outputDescribers as $outputDescriber) {
            if ($outputDescriber->supports($output)) {
                return $outputDescriber->describe($output, $this);
            }
        }

        throw new InvalidArgumentException(
            Psl\Str\format('Can not handle object to describe of type "%s"', $output::class),
        );
    }
}
