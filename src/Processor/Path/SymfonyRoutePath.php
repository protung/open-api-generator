<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor\Path;

use Speicher210\OpenApiGenerator\Model\FormDefinition;

final class SymfonyRoutePath implements Path
{
    private string $routeName;

    private string $tag;

    private string $summary;

    private ?string $description;

    private array $input;

    private array $responses;

    private array $security;

    private bool $deprecated;

    /**
     * @param string[] $security
     */
    public function __construct(
        string $routeName,
        string $tag,
        string $summary,
        ?string $description,
        array $input,
        array $responses,
        array $security,
        bool $deprecated = false
    ) {
        $this->routeName  = $routeName;
        $this->tag        = $tag;
        $this->summary    = $summary;
        $this->description = $description;
        $this->input      = $input;
        $this->responses  = $responses;
        $this->security   = $security;
        $this->deprecated = $deprecated;
    }

    public function routeName() : string
    {
        return $this->routeName;
    }

    public function tag() : string
    {
        return $this->tag;
    }

    public function summary() : string
    {
        return $this->summary;
    }

    public function description() : ?string
    {
        return $this->description;
    }

    /**
     * @return FormDefinition[]
     */
    public function input() : array
    {
        return $this->input;
    }

    public function responses() : array
    {
        return $this->responses;
    }

    public function security() : array
    {
        return $this->security;
    }

    public function isDeprecated() : bool
    {
        return $this->deprecated;
    }
}
