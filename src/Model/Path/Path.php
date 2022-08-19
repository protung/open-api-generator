<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path;

use Protung\OpenApiGenerator\Model\Callback;
use Protung\OpenApiGenerator\Model\Response;
use Protung\OpenApiGenerator\Model\Security\Reference;

interface Path
{
    public function tag(): string;

    public function summary(): string;

    public function description(): ?string;

    /**
     * @return Input[]
     */
    public function input(): array;

    public function addInput(Input $input): void;

    /**
     * @return Response[]
     */
    public function responses(): array;

    public function addResponse(Response $response): void;

    public function security(): Reference;

    public function isDeprecated(): bool;

    /**
     * @return Callback[]
     */
    public function callbacks(): array;
}
