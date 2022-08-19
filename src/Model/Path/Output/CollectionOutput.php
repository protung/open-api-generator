<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Protung\OpenApiGenerator\Model\Path\Output;

final class CollectionOutput implements Output
{
    private Output $output;

    private function __construct(Output $output)
    {
        $this->output = $output;
    }

    public static function forOutput(Output $output): self
    {
        return new self($output);
    }

    /**
     * @param class-string $className
     */
    public static function forClass(string $className): self
    {
        return new self(ObjectOutput::forClass($className));
    }

    public function output(): Output
    {
        return $this->output;
    }

    /**
     * @return array<mixed>
     */
    public function example(): array
    {
        return [$this->output->example()];
    }

    /**
     * {@inheritDoc}
     */
    public function contentTypes(): array
    {
        return $this->output->contentTypes();
    }
}
