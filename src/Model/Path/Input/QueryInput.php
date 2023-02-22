<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Input;

use Protung\OpenApiGenerator\Model\Path\InputLocation;
use Protung\OpenApiGenerator\Model\Path\IOField;

final class QueryInput extends SimpleInput
{
    private function __construct(IOField ...$fields)
    {
        parent::__construct(InputLocation::Query, ...$fields);
    }

    public static function withIOField(IOField $field): self
    {
        return new self($field);
    }
}
