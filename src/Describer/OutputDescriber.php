<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use InvalidArgumentException;
use Speicher210\OpenApiGenerator\Describer\ExampleDescriber\ExampleDescriber;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Model\Definition;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\RFC7807ErrorOutput;
use Speicher210\OpenApiGenerator\Model\Path\ReferencableOutput;
use function get_class;
use function sprintf;

final class OutputDescriber
{
    private const RESPONSE_CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    private const RESPONSE_CONTENT_TYPE_APPLICATION_PROBLEM_JSON = 'application/problem+json';

    private ObjectDescriber $objectDescriber;

    /** @var array<OutputDescriber\OutputDescriber> */
    private array $outputDescribers;

    public function __construct(
        ObjectDescriber $objectDescriber,
        FormFactory $formFactory,
        ExampleDescriber $exampleDescriber
    ) {
        $this->objectDescriber = $objectDescriber;

        $this->outputDescribers = [
            new OutputDescriber\ScalarOutputDescriber(),
            new OutputDescriber\SimpleOutputDescriber(),
            new OutputDescriber\CollectionOutputDescriber($this, $exampleDescriber),
            new OutputDescriber\PaginatedOutputDescriber($this),
            new OutputDescriber\FormErrorOutputDescriber($formFactory),
            new OutputDescriber\ObjectOutputDescriber($this->objectDescriber, $exampleDescriber),
        ];
    }

    /**
     * @return Reference|Schema
     */
    public function describe(Output $output) : SpecObjectInterface
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
            sprintf('Can not handle object to describe of type "%s"', get_class($output))
        );
    }

    public function contentType(Output $output) : string
    {
        if ($output instanceof ReferencableOutput) {
            return $this->contentType($output->output());
        }

        if ($output instanceof RFC7807ErrorOutput) {
            return self::RESPONSE_CONTENT_TYPE_APPLICATION_PROBLEM_JSON;
        }

        return self::RESPONSE_CONTENT_TYPE_APPLICATION_JSON;
    }
}
