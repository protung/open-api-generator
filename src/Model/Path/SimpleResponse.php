<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path;

use Speicher210\OpenApiGenerator\Model\Path\Output\ErrorResponse;

final class SimpleResponse implements Response
{
    private int $statusCode;

    private array $description;

    private ?Output $output;

    /**
     * @param string|string[] $description
     */
    public function __construct(int $statusCode, $description, ?Output $output = null)
    {
        $this->statusCode  = $statusCode;
        $this->description = (array) $description;
        $this->output      = $output;
    }

    public static function for200(Output $output) : self
    {
        return new self(200, ['Returned on success'], $output);
    }

    public static function for201(Output $output) : self
    {
        return new self(201, ['Returned on success'], $output);
    }

    public static function for202() : self
    {
        return new self(202, ['Returned when successfully accepted data']);
    }

    public static function for204() : self
    {
        return new self(204, ['Returned on success']);
    }

    /**
     * @param string|string[] $description
     */
    public static function for400($description = ['Returned when there is a validation error']) : self
    {
        return new self(400, $description);
    }

    public static function for401() : self
    {
        return new self(401, ['Returned if user is not authenticated'], ErrorResponse::for401());
    }

    /**
     * @param string|string[] $description
     */
    public static function for402($description) : self
    {
        return new self(402, $description, ErrorResponse::for402());
    }

    /**
     * @param string|string[] $description
     */
    public static function for403($description) : self
    {
        return new self(403, $description, ErrorResponse::for403());
    }

    /**
     * @param string|string[] $description
     */
    public static function for404($description) : self
    {
        return new self(404, $description, ErrorResponse::for404());
    }

    public static function for406() : self
    {
        return new self(
            406,
            ['Returned if response content type expected is not supported'],
            ErrorResponse::for406()
        );
    }

    public static function for415() : self
    {
        return new self(
            415,
            ['Returned if request payload format is not supported'],
            ErrorResponse::for415()
        );
    }

    public function statusCode() : int
    {
        return $this->statusCode;
    }

    public function description() : string
    {
        return \nl2br(\implode(\PHP_EOL, $this->description), false);
    }

    public function output() : ?Output
    {
        return $this->output;
    }
}
