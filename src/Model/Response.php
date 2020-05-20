<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

final class Response
{
    private int $statusCode;

    private array $description;

    private ?object $output;

    /**
     * @param string[] $description
     */
    public function __construct(int $statusCode, array $description, ?object $output = null)
    {
        $this->statusCode  = $statusCode;
        $this->description = $description;
        $this->output      = $output;
    }

    public static function for200(object $output): self
    {
        return new self(200, ['Returned on success'], $output);
    }

    public static function for201(object $output): self
    {
        return new self(201, ['Returned on success'], $output);
    }

    public static function for202(): self
    {
        return new self(202, ['Returned when successfully accepted data']);
    }

    public static function for204(): self
    {
        return new self(204, ['Returned on success']);
    }

    /**
     * @param string[] $description
     */
    public static function for400(array $description = ['Returned when there is a validation error']): self
    {
        return new self(400, $description);
    }

    public static function for401(): self
    {
        return new self(401, ['Returned if user is not authenticated'], ErrorResponseOutput::for401());
    }

    /**
     * @param string[] $description
     */
    public static function for402(array $description): self
    {
        return new self(402, $description, ErrorResponseOutput::for402());
    }

    /**
     * @param string[] $description
     */
    public static function for403(array $description): self
    {
        return new self(403, $description, ErrorResponseOutput::for403());
    }

    /**
     * @param string[] $description
     */
    public static function for404(array $description): self
    {
        return new self(404, $description, ErrorResponseOutput::for404());
    }

    public static function for406(): self
    {
        return new self(
            406,
            ['Returned if response content type expected is not supported'],
            ErrorResponseOutput::for406()
        );
    }

    public static function for415(): self
    {
        return new self(
            415,
            ['Returned if request payload format is not supported'],
            ErrorResponseOutput::for415()
        );
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string[]
     */
    public function description(): array
    {
        return $this->description;
    }

    public function descriptionText(): string
    {
        return \nl2br(\implode(\PHP_EOL, $this->description), false);
    }

    public function output(): ?object
    {
        return $this->output;
    }
}
