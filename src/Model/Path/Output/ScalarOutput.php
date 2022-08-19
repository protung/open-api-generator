<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Type;
use Psl;

final class ScalarOutput implements Output
{
    private string $type;

    private string $contentType;

    private bool|float|int|string|null $example;

    private function __construct(string $type, string $contentType)
    {
        Assert::inArray($type, Type::SCALAR_TYPES);

        $this->type        = $type;
        $this->contentType = $contentType;
        $this->example     = Psl\Type\nullable(Psl\Type\scalar())->coerce(Type::example($type));
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

    public function withExample(bool|float|int|string|null $example): self
    {
        $this->example = $example;

        return $this;
    }

    public function example(): bool|float|int|string|null
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
