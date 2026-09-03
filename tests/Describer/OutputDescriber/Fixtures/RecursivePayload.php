<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures;

use Symfony\Component\Validator\Constraints as Constraint;

final readonly class RecursivePayload
{
    public function __construct(
        #[Constraint\NotBlank]
        public string $name,
        #[Constraint\Valid]
        public RecursivePayload|null $child = null,
    ) {
    }
}
