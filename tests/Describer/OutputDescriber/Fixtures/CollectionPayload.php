<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures;

use Symfony\Component\Validator\Constraints as Constraint;

final readonly class CollectionPayload
{
    /**
     * @param list<PairRequest> $pairs
     */
    public function __construct(
        #[Constraint\Valid]
        public array $pairs,
    ) {
    }
}
