<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures;

use Symfony\Component\Validator\Constraints as Constraint;

final readonly class OptionalMembersRequest
{
    public function __construct(
        #[Constraint\NotBlank]
        public string $name,
        // Nullable, so it can be left out of the payload.
        public int|null $retryCount,
        // Has a default, so it can be left out of the payload.
        public bool $verbose = false,
        #[Constraint\Length(min: 3)]
        public string|null $nickname = null,
    ) {
    }
}
