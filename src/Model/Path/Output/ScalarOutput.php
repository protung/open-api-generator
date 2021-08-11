<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Type;

final class ScalarOutput implements Output
{
    private string $type;

    private string $contentType;

    /** @var bool|float|int|string|null */
    private $example;

    private function __construct(string $type, string $contentType)
    {
        Assert::inArray($type, Type::SCALAR_TYPES);

        $this->type        = $type;
        $this->contentType = $contentType;
        $this->example     = Type::example($type);
    }

    public static function json(string $type): self
    {
        return new self($type, Output::CONTENT_TYPE_APPLICATION_JSON);
    }

    public static function plainText(string $type): self
    {
        return new self($type, Output::CONTENT_TYPE_TEXT_PLAIN);
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @param bool|float|int|string|null $example
     */
    public function withExample($example): self
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

    /**
     * {@inheritDoc}
     */
    public function contentTypes(): array
    {
        return [$this->contentType];
    }
}
