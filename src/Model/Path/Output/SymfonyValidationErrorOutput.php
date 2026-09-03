<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Override;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Path\StatusCodeAwareOutput;

/**
 * Describes the problem details document Symfony returns for a bad request payload.
 * It is an RFC 7807 document extended with the "violations" member Symfony's ConstraintViolationListNormalizer produces.
 */
final class SymfonyValidationErrorOutput extends SimpleOutput implements StatusCodeAwareOutput
{
    private int $statusCode;

    private function __construct(int $statusCode)
    {
        $this->statusCode = $statusCode;

        parent::__construct(self::fieldsFor($statusCode), self::exampleFor($statusCode));
    }

    /**
     * The status code is taken from the response the output is attached to. The one set here is the
     * #[MapRequestPayload] default and only applies while the output stands on its own.
     */
    public static function create(): self
    {
        return new self(422);
    }

    #[Override]
    public function statusCode(): int
    {
        return $this->statusCode;
    }

    #[Override]
    public function withStatusCode(int $statusCode): static
    {
        $clone             = clone $this;
        $clone->statusCode = $statusCode;
        $clone->replaceFields(self::fieldsFor($statusCode), self::exampleFor($statusCode));

        return $clone;
    }

    /**
     * @return IOField[]
     */
    private static function fieldsFor(int $statusCode): array
    {
        $violations = IOField::arrayField(
            'violations',
            IOField::objectField(
                'violation',
                IOField::stringField('propertyPath'),
                IOField::stringField('title'),
                IOField::stringField('template'),
                // An open map of message placeholders, described as a free form object.
                IOField::objectField('parameters'),
            ),
        );

        // A payload which can not be parsed at all never reaches validation and is always answered with a
        // 400 carrying no violations, so only there can the member be missing. Any other status code this
        // document is returned with is reachable through a validation failure alone.
        if ($statusCode === 400) {
            $violations->asOptional();
        }

        return [
            IOField::stringField('type'),
            IOField::stringField('title'),
            IOField::integerField('status'),
            IOField::stringField('detail'),
            $violations,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function exampleFor(int $statusCode): array
    {
        return [
            'type' => 'https://symfony.com/errors/validation',
            'title' => 'Validation Failed',
            'status' => $statusCode,
            'detail' => 'pairingCode: This value is not valid.',
            'violations' => [
                [
                    'propertyPath' => 'pairingCode',
                    'title' => 'This value is not valid.',
                    'template' => 'This value should be of type {{ type }}.',
                    'parameters' => ['{{ type }}' => 'string'],
                ],
            ],
        ];
    }
}
