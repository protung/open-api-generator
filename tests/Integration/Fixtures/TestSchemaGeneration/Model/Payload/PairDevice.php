<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\Payload;

use Symfony\Component\Validator\Constraints as Constraint;

final readonly class PairDevice
{
    public function __construct(
        #[Constraint\NotBlank]
        public string $name,
        public int|null $firmwareVersion = null,
    ) {
    }
}
