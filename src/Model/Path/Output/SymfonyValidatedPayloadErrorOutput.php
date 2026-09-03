<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Override;
use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\StatusCodeAwareOutput;
use Psl;

/**
 * Describes the problem details document Symfony returns for a bad request payload, deriving what it can
 * from the mapped payload class instead of describing the violations as free form strings.
 *
 * Requires a ValidatorInterface to be passed to the OutputDescriber, otherwise there is no metadata to read.
 */
final class SymfonyValidatedPayloadErrorOutput implements StatusCodeAwareOutput
{
    /** @var class-string */
    private string $className;

    private int $statusCode;

    /** @var list<string> */
    private array $validationGroups = [];

    private bool $describesMessageTemplates = false;

    /** @var non-empty-list<string>|null */
    private array|null $contentTypes = null;

    /**
     * @param class-string $className
     */
    private function __construct(string $className, int $statusCode)
    {
        Assert::classExists($className);

        $this->className  = $className;
        $this->statusCode = $statusCode;
    }

    /**
     * @param class-string $className The class the endpoint maps the request payload to.
     */
    public static function forClass(string $className): self
    {
        // The status code is taken from the response the output is attached to. The one set here is the
        // #[MapRequestPayload] default and only applies while the output stands on its own.
        return new self($className, 422);
    }

    #[Override]
    public function withStatusCode(int $statusCode): static
    {
        $clone             = clone $this;
        $clone->statusCode = $statusCode;

        return $clone;
    }

    /**
     * Restricts the described message templates to the given validation groups, matching the "validationGroups"
     * the endpoint declares. Has no effect on the described property paths, see propertyPaths() on the describer.
     */
    public function withValidationGroups(string ...$validationGroups): self
    {
        $this->validationGroups = Psl\Vec\values($validationGroups);

        return $this;
    }

    /**
     * Also describes the message templates the payload constraints can produce.
     *
     * Opt in, because the described list is only as complete as the constraints declared on the payload class.
     * Validation performed anywhere else, a custom constraint holding its message somewhere unusual or an
     * Assert\Callback picking a message at runtime all produce templates which can not be read up front.
     */
    public function withMessageTemplates(): self
    {
        $this->describesMessageTemplates = true;

        return $this;
    }

    public function withContentTypes(string $contentType, string ...$contentTypes): self
    {
        $this->contentTypes = [$contentType, ...Psl\Vec\values($contentTypes)];

        return $this;
    }

    /**
     * @return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    #[Override]
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return list<string>
     */
    public function validationGroups(): array
    {
        return $this->validationGroups;
    }

    public function describesMessageTemplates(): bool
    {
        return $this->describesMessageTemplates;
    }

    #[Override]
    public function example(): mixed
    {
        // The example is derived from the payload class by the describer.
        return null;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function contentTypes(): array
    {
        return $this->contentTypes ?? [Output::CONTENT_TYPE_APPLICATION_JSON];
    }
}
