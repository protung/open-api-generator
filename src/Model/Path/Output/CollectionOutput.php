<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Model\Path\Output;

final class CollectionOutput implements Output
{
    private Output $output;

    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    public function output() : Output
    {
        return $this->output;
    }

    /**
     * @return array<mixed>
     */
    public function example() : array
    {
        return [$this->output->example()];
    }
}
