<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor\Path\Symfony;

use Speicher210\OpenApiGenerator\Model\Path\Input;
use Speicher210\OpenApiGenerator\Model\Response;
use Speicher210\OpenApiGenerator\Model\Security\Reference;
use Speicher210\OpenApiGenerator\Processor\Path\Path;

final class SymfonyRoutePath implements Path
{
    private string $routeName;

    private string $tag;

    private string $summary;

    private ?string $description;

    /** @var Input[] */
    private array $input;

    /** @var Response[] */
    private array $responses;

    private Reference $security;

    private bool $deprecated;

    /**
     * @param Input[]    $input
     * @param Response[] $responses
     */
    public function __construct(
        string $routeName,
        string $tag,
        string $summary,
        ?string $description,
        array $input,
        array $responses,
        ?Reference $security = null,
        bool $deprecated = false
    ) {
        $this->routeName   = $routeName;
        $this->tag         = $tag;
        $this->summary     = $summary;
        $this->description = $description;
        $this->input       = $input;
        $this->responses   = $responses;
        $this->security    = $security ?? Reference::none();
        $this->deprecated  = $deprecated;
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
     * @return Input[]
     */
    public function input() : array
    {
        return $this->input;
    }

    /**
     * @return Response[]
     */
    public function responses() : array
    {
        return $this->responses;
    }

    public function security() : Reference
    {
        return $this->security;
    }

    public function isDeprecated() : bool
    {
        return $this->deprecated;
    }
}
