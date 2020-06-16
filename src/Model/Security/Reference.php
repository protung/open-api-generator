<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Security;

use function array_map;

final class Reference
{
    /** @var array<string,array<mixed>> */
    private array $references;

    /**
     * @param array<string,array<mixed>> $references
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
     * @param string[] $references
     */
    public static function fromReferences(array $references): self
    {
        return new self(
            array_map(static fn ($value) => [$value => []], $references)
        );
    }

    /**
     * @return array<string,mixed[]>
     */
    public function references(): array
    {
        return $this->references;
    }
}
