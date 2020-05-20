<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

final class ErrorResponseOutput
{
    private int $errorCode;

    private string $message;

    public function __construct(int $errorCode, string $message)
    {
        $this->errorCode = $errorCode;
        $this->message   = $message;
    }

    public static function for401(): self
    {
        return new self(401, 'Unauthorized');
    }

    public static function for402(): self
    {
        return new self(402, 'Subscription feature required');
    }

    public static function for403(): self
    {
        return new self(403, 'Forbidden');
    }

    public static function for404(): self
    {
        return new self(404, 'Not Found');
    }

    public static function for406(): self
    {
        return new self(406, 'Not Acceptable');
    }

    public static function for415(): self
    {
        return new self(415, 'Unsupported Media Type');
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
     * @return mixed[]
     */
    public function asExample(): array
    {
        return [
            'code' => $this->errorCode,
            'message' => $this->message,
        ];
    }

    /**
     * @return mixed[]
     */
    public function asModel(): array
    {
        return [
            'code' => ['type' => 'integer'],
            'message' => ['type' => 'string'],
        ];
    }
}
