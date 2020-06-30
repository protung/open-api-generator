<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Input;

use Speicher210\OpenApiGenerator\Model\Path\IOField;

final class QueryInput extends SimpleInput
{
    private function __construct(IOField ...$fields)
    {
        parent::__construct(self::LOCATION_QUERY, ...$fields);
    }

    public static function withIOField(IOField $field): self
    {
        return new self($field);
    }
}
