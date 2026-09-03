<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures;

use Symfony\Component\Validator\Constraints as Constraint;

final readonly class PairRequest
{
    public function __construct(
        #[Constraint\NotBlank(normalizer: 'trim')]
        #[Constraint\Length(max: 26)]
        public string $pairingCode,
    ) {
    }
}
