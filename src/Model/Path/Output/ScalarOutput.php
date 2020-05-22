<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Type;

final class ScalarOutput implements Output
{
    private string $type;

    /** @var bool|float|int|string|null */
    private $example;

    public function __construct(string $type)
    {
        Assert::inArray($type, Type::SCALAR_TYPES);

        $this->type    = $type;
        $this->example = Type::example($type);
    }

    public function type() : string
    {
        return $this->type;
    }

    public function withExample(string $example) : self
    {
        $this->example = $example;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function example()
    {
        return $this->example;
    }
}
