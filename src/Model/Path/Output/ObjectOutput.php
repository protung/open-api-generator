<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\OutputDescribableAsReference;
use Speicher210\OpenApiGenerator\Model\Path\SerializationGroupAwareOutput;
use function array_merge;
use function array_unique;

final class ObjectOutput implements SerializationGroupAwareOutput, OutputDescribableAsReference
{
    private string $className;

    /** @var string[] */
    private array $serializationGroups;

    private bool $describeAsReference;

    /**
     * @param string[] $serializationGroups
     */
    private function __construct(string $className, array $serializationGroups, bool $describeAsReference = false)
    {
        Assert::classExists($className);

        $this->className           = $className;
        $this->serializationGroups = $serializationGroups;
        $this->describeAsReference = $describeAsReference;
    }

    public static function forClass(string $className, bool $describeAsReference = false) : self
    {
        return new self($className, SerializationGroupAwareOutput::DEFAULT_SERIALIZATION_GROUPS, $describeAsReference);
    }

    /**
     * @param string[] $groups
     */
    public static function withSerializationGroups(
        string $className,
        array $groups,
        bool $describeAsReference = false
    ) : self {
        $groups = array_unique(
            array_merge(
                $groups,
                SerializationGroupAwareOutput::DEFAULT_SERIALIZATION_GROUPS
            )
        );

        return new self($className, $groups, $describeAsReference);
    }

    public function className() : string
    {
        return $this->className;
    }

    public function example() : void
    {
        // TODO: Implement example() method.
    }

    /**
     * {@inheritDoc}
     */
    public function serializationGroups() : array
    {
        return $this->serializationGroups;
    }

    public function shouldBeDescribedAsReference() : bool
    {
        return $this->describeAsReference;
    }
}
