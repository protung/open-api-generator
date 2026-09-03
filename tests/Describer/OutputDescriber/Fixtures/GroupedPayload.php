<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures;

use Symfony\Component\Validator\Constraints as Constraint;

final readonly class GroupedPayload
{
    public function __construct(
        #[Constraint\NotBlank(message: 'The name is required.')]
        public string $name,
        #[Constraint\NotBlank(message: 'The nickname is required.', groups: ['strict'])]
        public string $nickname,
    ) {
    }
}
