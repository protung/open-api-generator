<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\SerializationGroupAwareOutput;
use function array_merge;
use function array_unique;

final class ObjectOutput implements SerializationGroupAwareOutput
{
    private string $className;

    /** @var string[] */
    private array $serializationGroups;

    /**
     * @param string[] $serializationGroups
     */
    private function __construct(string $className, array $serializationGroups)
    {
        Assert::classExists($className);

        $this->className           = $className;
        $this->serializationGroups = $serializationGroups;
    }

    public static function forClass(string $className) : self
    {
        return new self($className, SerializationGroupAwareOutput::DEFAULT_SERIALIZATION_GROUPS);
    }

    /**
     * @param string[] $groups
     */
    public static function withSerializationGroups(string $className, array $groups) : self
    {
        $groups = array_unique(
            array_merge(
                $groups,
                SerializationGroupAwareOutput::DEFAULT_SERIALIZATION_GROUPS
            )
        );

        return new self($className, $groups);
    }

    public function className() : string
    {
        return $this->className;
    }

    /**
     * @return mixed
     */
    public function example()
    {
        // TODO: Implement example() method.
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function serializationGroups() : array
    {
        return $this->serializationGroups;
    }
}
