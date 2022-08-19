<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Protung\OpenApiGenerator\Model\Definition;
use Protung\OpenApiGenerator\Model\Path\ReferencableOutput as ReferencableOutputInterface;
use Protung\OpenApiGenerator\Resolver\DefinitionName;

final class ReferencableOutput implements ReferencableOutputInterface
{
    private const REFERENCE_PREFIX_SCHEMA = '#/components/schemas/';

    private ObjectOutput $output;

    private string $referencePath;

    private function __construct(ObjectOutput $output, string $referencePath)
    {
        $this->output        = $output;
        $this->referencePath = $referencePath;
    }

    public static function forSchema(ObjectOutput $output, ?string $name = null): self
    {
        $name ??= DefinitionName::getName(Definition::fromObjectOutput($output));

        return new self($output, self::REFERENCE_PREFIX_SCHEMA . $name);
    }

    public function referencePath(): string
    {
        return $this->referencePath;
    }

    public function output(): ObjectOutput
    {
        return $this->output;
    }

    public function example(): mixed
    {
        return $this->output->example();
    }

    /**
     * {@inheritDoc}
     */
    public function contentTypes(): array
    {
        return $this->output->contentTypes();
    }
}
