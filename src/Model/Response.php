<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model;

use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\Output\FormErrorOutput;
use Protung\OpenApiGenerator\Model\Path\Output\RFC7807ErrorOutput;
use Symfony\Component\Form\FormTypeInterface;

use function implode;
use function nl2br;

use const PHP_EOL;

final class Response
{
    private int $statusCode;

    /** @var string[] */
    private array $description;

    /** @var Output[] */
    private array $outputs;

    /**
     * @param string[] $description
     */
    public function __construct(int $statusCode, array $description, Output ...$outputs)
    {
        $this->statusCode  = $statusCode;
        $this->description = $description;
        $this->outputs     = $outputs;
    }

    public static function for200(Output ...$outputs): self
    {
        return new self(200, ['Returned on success'], ...$outputs);
    }

    public static function for201(Output ...$outputs): self
    {
        return new self(201, ['Returned on success'], ...$outputs);
    }

    public static function for202(): self
    {
        return new self(202, ['Returned when successfully accepted data']);
    }

    public static function for204(): self
    {
        return new self(204, ['Returned on success']);
    }

    public static function for400(Output ...$outputs): self
    {
        return new self(400, ['Returned when there is a validation error'], ...$outputs);
    }

    /**
     * @param class-string<FormTypeInterface<mixed>> $formType
     * @param array<string, mixed>                   $formOptions
     */
    public static function for400WithForm(string $formType, array $formOptions = []): self
    {
        return self::for400(
            new FormErrorOutput(new FormDefinition($formType, $formOptions)),
        );
    }

    public static function for401(): self
    {
        return new self(401, ['Authentication is missing, invalid or expired'], RFC7807ErrorOutput::for401());
    }

    public static function for402(): self
    {
        return new self(402, ['Returned when payment is required'], RFC7807ErrorOutput::for402());
    }

    public static function for403(): self
    {
        return new self(403, ['Returned when operation is prohibited'], RFC7807ErrorOutput::for403());
    }

    public static function for404(): self
    {
        return new self(404, ['Returned when resource is not found'], RFC7807ErrorOutput::for404());
    }

    public static function for405(): self
    {
        return new self(405, ['Returned when HTTP method is not allowed'], RFC7807ErrorOutput::for405());
    }

    public static function for406(): self
    {
        return new self(
            406,
            ['Returned when response content type expected is not supported'],
            RFC7807ErrorOutput::for406(),
        );
    }

    public static function for409(): self
    {
        return new self(
            409,
            ['Returned when conflict with current state of the server occurred.'],
            RFC7807ErrorOutput::for409(),
        );
    }

    public static function for415(): self
    {
        return new self(
            415,
            ['Returned when request payload format is not supported'],
            RFC7807ErrorOutput::for415(),
        );
    }

    public static function for423(): self
    {
        return new self(
            423,
            ['Returned when resource is locked'],
            RFC7807ErrorOutput::for423(),
        );
    }

    public static function for500(): self
    {
        return new self(
            500,
            ['Returned on server error'],
            RFC7807ErrorOutput::for500(),
        );
    }

    /**
     * @param string[]|string $description
     */
    public function withDescription(array|string $description): self
    {
        $this->description = (array) $description;

        return $this;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function description(): string
    {
        return nl2br(implode(PHP_EOL, $this->description), false);
    }

    /**
     * @return Output[]
     */
    public function outputs(): array
    {
        return $this->outputs;
    }
}
