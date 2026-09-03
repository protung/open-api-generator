<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Protung\OpenApiGenerator\Model\Path\IOField;

/**
 * Describes the problem details document Symfony returns for a bad request payload.
 * It is an RFC 7807 document extended with the "violations" member Symfony's ConstraintViolationListNormalizer produces.
 */
final class SymfonyValidationErrorOutput extends SimpleOutput
{
    private function __construct(int $statusCode)
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

        parent::__construct(
            [
                IOField::stringField('type'),
                IOField::stringField('title'),
                IOField::integerField('status'),
                IOField::stringField('detail'),
                $violations,
            ],
            [
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
            ],
        );
    }

    /**
     * @param int $statusCode The status code Symfony answers a validation failure with. Defaults to the
     *                        #[MapRequestPayload] default, override it when the endpoint declares another
     *                        one through "validationFailedStatusCode".
     */
    public static function create(int $statusCode = 422): self
    {
        return new self($statusCode);
    }
}
