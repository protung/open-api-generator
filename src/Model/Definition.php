<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use Speicher210\OpenApiGenerator\Model\Path\Output\ObjectOutput;
use function sort;

final class Definition
{
    private string $className;

    /** @var string[] */
    private array $serializationGroups;

    private ?object $exampleObject;

    /**
     * @param string[] $serializationGroups
     */
    public function __construct(string $className, array $serializationGroups, ?object $exampleObject = null)
    {
        $this->className           = $className;
        $this->serializationGroups = $serializationGroups;
        sort($this->serializationGroups);

        $this->exampleObject = $exampleObject;
    }

    public static function fromObjectOutput(ObjectOutput $objectOutput) : self
    {
        return new self(
            $objectOutput->className(),
            $objectOutput->serializationGroups(),
            $objectOutput->example()
        );
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

    public function exampleObject() : ?object
    {
        return $this->exampleObject;
    }

    public function equals(Definition $other) : bool
    {
        return $other->className === $this->className && $other->serializationGroups === $this->serializationGroups;
    }
}
