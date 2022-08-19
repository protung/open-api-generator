<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Input;

use Protung\OpenApiGenerator\Model\Path\IOField;

final class PathInput extends SimpleInput
{
    private function __construct(IOField ...$fields)
    {
        parent::__construct(self::LOCATION_PATH, ...$fields);
    }

    public static function withIOFields(IOField ...$fields): self
    {
        return new self(...$fields);
    }
}
