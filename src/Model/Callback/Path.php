<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Callback;

use Override;
use Protung\OpenApiGenerator\Model\Callback;
use Protung\OpenApiGenerator\Model\Path\Input;
use Protung\OpenApiGenerator\Model\Response;
use Protung\OpenApiGenerator\Model\Security\Reference;

final class Path implements \Protung\OpenApiGenerator\Model\Path\Path
{
    private string $tag;

    private string $summary;

    private string|null $description;

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
        string|null $description,
        array $input,
        array $responses,
        Reference|null $security = null,
        bool $deprecated = false,
    ) {
        $this->tag         = $tag;
        $this->summary     = $summary;
        $this->description = $description;
        $this->input       = $input;
        $this->responses   = $responses;
        $this->security    = $security ?? Reference::none();
        $this->deprecated  = $deprecated;
    }

    #[Override]
    public function tag(): string
    {
        return $this->tag;
    }

    #[Override]
    public function summary(): string
    {
        return $this->summary;
    }

    #[Override]
    public function description(): string|null
    {
        return $this->description;
    }

    /**
     * @return Input[]
     */
    #[Override]
    public function input(): array
    {
        return $this->input;
    }

    #[Override]
    public function addInput(Input $input): void
    {
        $this->input[] = $input;
    }

    /**
     * @return Response[]
     */
    #[Override]
    public function responses(): array
    {
        return $this->responses;
    }

    #[Override]
    public function addResponse(Response $response): void
    {
        $this->responses[] = $response;
    }

    #[Override]
    public function security(): Reference
    {
        return $this->security;
    }

    #[Override]
    public function isDeprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * @return Callback[]
     */
    #[Override]
    public function callbacks(): array
    {
        return [];
    }
}
