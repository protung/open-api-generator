<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use function sort;

final class Definition
{
    private string $className;

    /** @var string[] */
    private array $serializationGroups;

    /**
     * @param string[] $serializationGroups
     */
    public function __construct(string $className, array $serializationGroups)
    {
        $this->className           = $className;
        $this->serializationGroups = $serializationGroups;
        sort($this->serializationGroups);
    }

    public function className() : string
    {
        return $this->className;
    }

    /**
     * @return string[]
     */
    public function serializationGroups() : array
    {
        return $this->serializationGroups;
    }

    public function equals(Definition $other) : bool
    {
        return $other->className === $this->className && $other->serializationGroups === $this->serializationGroups;
    }
}
