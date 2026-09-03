<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures;

use Symfony\Component\Validator\Constraints as Constraint;

final readonly class PairRequest
{
    public function __construct(
        #[Constraint\NotBlank(normalizer: 'trim')]
        #[Constraint\Length(max: 26)]
        public string $pairingCode,
        // Carries no constraint on purpose, the denormalizer can still report a type violation for it.
        public int|null $retryCount = null,
    ) {
    }
}
