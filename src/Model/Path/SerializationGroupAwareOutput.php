<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path;

interface SerializationGroupAwareOutput extends Output
{
    public const DEFAULT_SERIALIZATION_GROUPS = ['Default'];

    /**
     * @return string[]
     */
    public function serializationGroups() : array;
}
