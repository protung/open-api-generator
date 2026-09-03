<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use InvalidArgumentException;
use Protung\OpenApiGenerator\Describer\ExampleDescriber\ExampleDescriber;
use Protung\OpenApiGenerator\Describer\Form\FormFactory;
use Protung\OpenApiGenerator\Model\Definition;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\ReferencableOutput;
use Psl;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class OutputDescriber
{
    private ObjectDescriber $objectDescriber;

    /** @var array<OutputDescriber\OutputDescriber> */
    private array $outputDescribers;

    /**
     * @param ValidatorInterface|null $validator Enables describing a SymfonyValidatedPayloadErrorOutput, which
     *                                           needs the validator metadata of the mapped payload class.
     */
    public function __construct(
        ObjectDescriber $objectDescriber,
        FormFactory $formFactory,
        ExampleDescriber $exampleDescriber,
        ValidatorInterface|null $validator = null,
    ) {
        $this->objectDescriber = $objectDescriber;

        $this->outputDescribers = [
            new OutputDescriber\ScalarOutputDescriber(),
            new OutputDescriber\SimpleOutputDescriber(),
            new OutputDescriber\FileOutputDescriber(),
            new OutputDescriber\CollectionOutputDescriber($this, $exampleDescriber),
            new OutputDescriber\PaginatedOutputDescriber($this),
            new OutputDescriber\FormErrorOutputDescriber($formFactory),
            new OutputDescriber\ObjectOutputDescriber($this->objectDescriber, $exampleDescriber),
        ];

        if ($validator === null) {
            return;
        }

        $this->outputDescribers[] = new OutputDescriber\SymfonyValidatedPayloadErrorOutputDescriber($validator);
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
                return $outputDescriber->describe($output);
            }
        }

        throw new InvalidArgumentException(
            Psl\Str\format('Can not handle object to describe of type "%s"', $output::class),
        );
    }
}
