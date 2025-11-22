<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Override;
use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\SerializationGroupAwareOutput;
use Psl;

use function array_merge;
use function array_unique;

final class ObjectOutput implements SerializationGroupAwareOutput
{
    /** @var class-string */
    private string $className;

    /** @var list<string> */
    private array $serializationGroups;

    private object|null $exampleObject = null;

    /**
     * @param class-string $className
     * @param list<string> $serializationGroups
     */
    private function __construct(string $className, array $serializationGroups)
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        Assert::classExists($className);

        $this->className           = $className;
        $this->serializationGroups = $serializationGroups;
    }

    /**
     * @param class-string $className
     */
    public static function forClass(string $className): self
    {
        return new self($className, SerializationGroupAwareOutput::DEFAULT_SERIALIZATION_GROUPS);
    }

    /**
     * @param class-string $className
     * @param list<string> $groups
     */
    public static function withSerializationGroups(string $className, array $groups): self
    {
        $groups = array_unique(
            array_merge(
                $groups,
                SerializationGroupAwareOutput::DEFAULT_SERIALIZATION_GROUPS,
            ),
        );

        return new self($className, Psl\Vec\values($groups));
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    public function withExample(object $exampleObject): self
    {
        $exampleObject = Psl\Type\instance_of($this->className)->coerce($exampleObject);

        $this->exampleObject = $exampleObject;

        return $this;
    }

    #[Override]
    public function example(): object|null
    {
        return $this->exampleObject;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function serializationGroups(): array
    {
        return $this->serializationGroups;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function contentTypes(): array
    {
        return [Output::CONTENT_TYPE_APPLICATION_JSON];
    }
}
