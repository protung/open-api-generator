<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path;

use NoDiscard;
use Protung\OpenApiGenerator\Model\Callback;
use Protung\OpenApiGenerator\Model\Response;
use Protung\OpenApiGenerator\Model\Security\Reference;

interface Path
{
    public function tag(): string;

    public function summary(): string;

    public function description(): string|null;

    /**
     * @return Input[]
     */
    public function input(): array;

    /**
     * Returns a copy of the path with the given inputs appended to its own.
     */
    #[NoDiscard]
    public function withAddedInputs(Input $input, Input ...$inputs): static;

    /**
     * @return Response[]
     */
    public function responses(): array;

    /**
     * Returns a copy of the path with the given responses appended to its own.
     */
    #[NoDiscard]
    public function withAddedResponses(Response $response, Response ...$responses): static;

    public function security(): Reference;

    public function isDeprecated(): bool;

    /**
     * @return Callback[]
     */
    public function callbacks(): array;
}
