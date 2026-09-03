<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path;

/**
 * An output whose shape depends on the status code it is returned with.
 *
 * The status code is not something the output can be told, it is decided by the response the output is
 * attached to, so Response passes its own down to every output implementing this.
 */
interface StatusCodeAwareOutput extends Output
{
    public function statusCode(): int;

    /**
     * Returns a copy rather than mutating, so the same output can be attached to more than one response
     * without the last one deciding the status code of all of them.
     */
    public function withStatusCode(int $statusCode): static;
}
