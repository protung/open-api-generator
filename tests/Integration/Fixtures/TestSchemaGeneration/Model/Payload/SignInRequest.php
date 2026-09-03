<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\Payload;

use SensitiveParameter;
use Symfony\Component\Validator\Constraints as Constraint;

final readonly class SignInRequest
{
    public function __construct(
        #[Constraint\NotBlank(normalizer: 'trim')]
        #[Constraint\Length(max: 100)]
        public string $username,
        #[Constraint\NotBlank]
        #[SensitiveParameter]
        public string $password,
        public bool $rememberMe = false,
    ) {
    }
}
