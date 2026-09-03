<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Input;

use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Model\Path\InputLocation;

/**
 * Describes the request body from the class the endpoint maps its payload to, so the documented body
 * and the payload can not drift apart.
 *
 * Deliberately not a SimpleInput: SimpleInputDescriber claims every SimpleInput and the first
 * describer to support an input wins, so extending it would hide this one.
 *
 * Requires a ValidatorInterface to be passed to the describer, otherwise there is no metadata to read.
 * Use BodyInput::withIOFields() for a body which should not follow the payload class.
 */
final class SymfonyMappedPayloadInput extends BaseInput
{
    /** @var class-string */
    private string $className;

    /**
     * @param class-string $className
     */
    private function __construct(string $className)
    {
        Assert::classExists($className);

        $this->className = $className;

        $this->setLocation(InputLocation::Body);
    }

    /**
     * @param class-string $className The class the endpoint maps the request payload to.
     */
    public static function forClass(string $className): self
    {
        return new self($className);
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }
}
