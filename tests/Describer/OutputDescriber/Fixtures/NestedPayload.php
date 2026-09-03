<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures;

use Symfony\Component\Validator\Constraints as Constraint;

final readonly class NestedPayload
{
    public function __construct(
        #[Constraint\NotBlank]
        public string $name,
        #[Constraint\Valid]
        public PairRequest $pair,
    ) {
    }
}
