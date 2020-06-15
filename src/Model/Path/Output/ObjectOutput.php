<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\SerializationGroupAwareOutput;
use function array_merge;
use function array_unique;

final class ObjectOutput implements SerializationGroupAwareOutput
{
    /** @var class-string */
    private string $className;

    /** @var string[] */
    private array $serializationGroups;

    private ?object $exampleObject = null;

    /**
     * @param class-string $className
     * @param string[]     $serializationGroups
     */
    private function __construct(string $className, array $serializationGroups)
    {
        Assert::classExists($className);

        $this->className           = $className;
        $this->serializationGroups = $serializationGroups;
    }

    /**
     * @param class-string $className
     */
    public static function forClass(string $className) : self
    {
        return new self($className, SerializationGroupAwareOutput::DEFAULT_SERIALIZATION_GROUPS);
    }

    /**
     * @param class-string $className
     * @param string[]     $groups
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

    /**
     * @return class-string
     */
    public function className() : string
    {
        return $this->className;
    }

    public function withExample(object $exampleObject) : self
    {
        Assert::isInstanceOf($exampleObject, $this->className);

        $this->exampleObject = $exampleObject;

        return $this;
    }

    public function example() : ?object
    {
        return $this->exampleObject;
    }

    /**
     * {@inheritDoc}
     */
    public function serializationGroups() : array
    {
        return $this->serializationGroups;
    }
}
