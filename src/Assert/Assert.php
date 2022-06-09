<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Assert;

use Speicher210\OpenApiGenerator\Assert\Exception\InvalidArgument;

final class Assert extends \Webmozart\Assert\Assert
{
    /**
     * @param string $message
     *
     * @psalm-pure
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected static function reportInvalidArgument($message): never
    {
        throw new InvalidArgument($message);
    }
}
