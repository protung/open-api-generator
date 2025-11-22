<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Output;

use Override;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Path\Output;

final class RFC7807ErrorOutput extends SimpleOutput
{
    private function __construct(int $errorCode, string $message)
    {
        parent::__construct(
            [
                IOField::stringField('type'),
                IOField::stringField('title'),
                IOField::integerField('status'),
                IOField::stringField('detail'),
            ],
            [
                'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
                'title' => 'An error occurred',
                'status' => $errorCode,
                'detail' => $message,
            ],
        );
    }

    public static function create(int $errorCode, string $message): self
    {
        return new self($errorCode, $message);
    }

    public static function for401(): self
    {
        return new self(401, 'Unauthorized');
    }

    public static function for402(): self
    {
        return new self(402, 'Payment Required');
    }

    public static function for403(): self
    {
        return new self(403, 'Forbidden');
    }

    public static function for404(): self
    {
        return new self(404, 'Not Found');
    }

    public static function for405(): self
    {
        return new self(405, 'Method Not Allowed');
    }

    public static function for406(): self
    {
        return new self(406, 'Not Acceptable');
    }

    public static function for409(): self
    {
        return new self(409, 'Conflict');
    }

    public static function for415(): self
    {
        return new self(415, 'Unsupported Media Type');
    }

    public static function for423(): self
    {
        return new self(423, 'Locked');
    }

    public static function for428(): self
    {
        return new self(428, 'Precondition Required');
    }

    public static function for500(): self
    {
        return new self(500, 'Internal Server Error');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function contentTypes(): array
    {
        return [Output::CONTENT_TYPE_APPLICATION_PROBLEM_JSON];
    }
}
