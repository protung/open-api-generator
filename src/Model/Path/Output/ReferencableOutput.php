<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Model\Definition;
use Speicher210\OpenApiGenerator\Model\Path\ReferencableOutput as ReferencableOutputInterface;
use Speicher210\OpenApiGenerator\Resolver\DefinitionName;

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

    public static function forSchema(ObjectOutput $output, ?string $name = null) : self
    {
        $name ??= DefinitionName::getName(new Definition($output->className(), $output->serializationGroups()));

        return new self($output, self::REFERENCE_PREFIX_SCHEMA . $name);
    }

    public function referencePath() : string
    {
        return $this->referencePath;
    }

    public function output() : ObjectOutput
    {
        return $this->output;
    }

    /**
     * @return mixed
     */
    public function example()
    {
        return $this->output->example();
    }
}
