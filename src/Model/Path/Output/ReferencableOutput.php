<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Model\Path\ReferencableOutput as ReferencableOutputInterface;

final class ReferencableOutput implements ReferencableOutputInterface
{
    private ObjectOutput $output;

    private function __construct(ObjectOutput $output)
    {
        $this->output = $output;
    }

    public static function forOutput(ObjectOutput $output) : self
    {
        return new self($output);
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
