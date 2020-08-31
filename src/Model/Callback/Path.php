<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Callback;

use Speicher210\OpenApiGenerator\Model\Callback;
use Speicher210\OpenApiGenerator\Model\Path\Input;
use Speicher210\OpenApiGenerator\Model\Response;
use Speicher210\OpenApiGenerator\Model\Security\Reference;

final class Path implements \Speicher210\OpenApiGenerator\Model\Path\Path
{
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
        string $tag,
        string $summary,
        ?string $description,
        array $input,
        array $responses,
        ?Reference $security = null,
        bool $deprecated = false
    ) {
        $this->tag         = $tag;
        $this->summary     = $summary;
        $this->description = $description;
        $this->input       = $input;
        $this->responses   = $responses;
        $this->security    = $security ?? Reference::none();
        $this->deprecated  = $deprecated;
    }

    public function tag(): string
    {
        return $this->tag;
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return Input[]
     */
    public function input(): array
    {
        return $this->input;
    }

    public function addInput(Input $input): void
    {
        $this->input[] = $input;
    }

    /**
     * @return Response[]
     */
    public function responses(): array
    {
        return $this->responses;
    }

    public function addResponse(Response $response): void
    {
        $this->responses[] = $response;
    }

    public function security(): Reference
    {
        return $this->security;
    }

    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * @return Callback[]
     */
    public function callbacks(): array
    {
        return [];
    }
}
