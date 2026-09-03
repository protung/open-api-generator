<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures;

final readonly class NestedPayloadRequest
{
    public function __construct(
        public PairRequest $pair,
    ) {
    }
}
