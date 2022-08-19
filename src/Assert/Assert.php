<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Assert;

use Protung\OpenApiGenerator\Assert\Exception\InvalidArgument;

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
