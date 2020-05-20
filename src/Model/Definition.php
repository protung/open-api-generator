<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

final class Definition
{
    private string $className;

    /** @var string[] */
    private array $serializationGroups;

    public function __construct(string $className, array $serializationGroups)
    {
        $this->className = $className;
        $this->serializationGroups = $serializationGroups;
    }

    public function className(): string
    {
        return $this->className;
    }

    public function serializationGroups(): array
    {
        return $this->serializationGroups;
    }

    public function hash(): string
    {
        return \md5(\serialize([$this->className, $this->serializationGroups]));
    }
}
