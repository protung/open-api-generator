<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\InputDescriber\Fixtures;

use Symfony\Component\Validator\Constraints as Constraint;

final readonly class SaveCopyTextActivityRequest
{
    public function __construct(
        #[Constraint\NotBlank(normalizer: 'trim')]
        #[Constraint\Length(min: 1, max: 65_535)]
        public string $content,
        #[Constraint\NotBlank(normalizer: 'trim')]
        #[Constraint\Length(max: 8_192)]
        public string $pageUrl,
    ) {
    }
}
