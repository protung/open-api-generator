<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path;

interface SerializationGroupAwareOutput extends Output
{
    public const DEFAULT_SERIALIZATION_GROUPS = ['Default'];

    /**
     * @return list<string>
     */
    public function serializationGroups(): array;
}
