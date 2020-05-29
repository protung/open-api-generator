<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\FormErrorOutput;
use Speicher210\OpenApiGenerator\Model\Path\Output\RFC7807ErrorOutput;
use function implode;
use function nl2br;
use const PHP_EOL;

final class Response
{
    private int $statusCode;

    /** @var string[] */
    private array $description;

    private ?Output $output;

    /**
     * @param string[] $description
     */
    public function __construct(int $statusCode, array $description, ?Output $output = null)
    {
        $this->statusCode  = $statusCode;
        $this->description = $description;
        $this->output      = $output;
    }

    /**
     * @param string[]|string $description
     */
    public static function for200(Output $output, $description = ['Returned on success']) : self
    {
        return new self(200, (array) $description, $output);
    }

    /**
     * @param string[]|string $description
     */
    public static function for201(Output $output, $description = ['Returned on success']) : self
    {
        return new self(201, (array) $description, $output);
    }

    /**
     * @param string[]|string $description
     */
    public static function for202($description = ['Returned when successfully accepted data']) : self
    {
        return new self(202, (array) $description);
    }

    /**
     * @param string[]|string $description
     */
    public static function for204($description = ['Returned on success']) : self
    {
        return new self(204, (array) $description);
    }

    /**
     * @param string[]|string $description
     */
    public static function for400(Output $output, $description = ['Returned when there is a validation error']) : self
    {
        return new self(400, (array) $description, $output);
    }

    /**
     * @param string[] $validationGroups
     */
    public static function for400WithForm(string $formType, array $validationGroups = []) : self
    {
        return self::for400(
            new FormErrorOutput(new FormDefinition($formType, $validationGroups))
        );
    }

    /**
     * @param string[]|string $description
     */
    public static function for401($description = ['Authentication is missing, invalid or expired']) : self
    {
        return new self(401, (array) $description, RFC7807ErrorOutput::for401());
    }

    /**
     * @param string[]|string $description
     */
    public static function for403($description = ['Returned when operation is prohibited']) : self
    {
        return new self(403, (array) $description, RFC7807ErrorOutput::for403());
    }

    /**
     * @param string[]|string $description
     */
    public static function for404($description = ['Returned when resource is not found']) : self
    {
        return new self(404, (array) $description, RFC7807ErrorOutput::for404());
    }

    /**
     * @param string[]|string $description
     */
    public static function for406($description = ['Returned when response content type expected is not supported']) : self
    {
        return new self(
            406,
            (array) $description,
            RFC7807ErrorOutput::for406()
        );
    }

    /**
     * @param string[]|string $description
     */
    public static function for415($description = ['Returned when request payload format is not supported']) : self
    {
        return new self(
            415,
            (array) $description,
            RFC7807ErrorOutput::for415()
        );
    }

    /**
     * @param string[]|string $description
     */
    public static function for500($description = ['Returned on server error']) : self
    {
        return new self(
            500,
            (array) $description,
            RFC7807ErrorOutput::for500()
        );
    }

    public function statusCode() : int
    {
        return $this->statusCode;
    }

    public function description() : string
    {
        return nl2br(implode(PHP_EOL, $this->description), false);
    }

    public function output() : ?Output
    {
        return $this->output;
    }
}
