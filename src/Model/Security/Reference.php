<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Security;

use Psl\Vec;

final class Reference
{
    /** @var list<array<string, array<mixed>>> */
    private array $references;

    /**
     * @param list<array<string, array<mixed>>> $references
     */
    private function __construct(array $references)
    {
        $this->references = $references;
    }

    public static function none(): self
    {
        return new self([]);
    }

    public static function fromString(string $reference): self
    {
        return self::fromReferences([$reference]);
    }

    /**
     * @param list<string> $references
     */
    public static function fromReferences(array $references): self
    {
        return new self(
            Vec\map(
                $references,
                static fn (string $value): array => [$value => []]
            )
        );
    }

    /**
     * @return list<array<string, array<mixed>>>
     */
    public function references(): array
    {
        return $this->references;
    }
}
