<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Input;

use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\Input;

abstract class BaseInput implements Input
{
    private string $location;

    final protected function setLocation(string $location) : void
    {
        Assert::inArray($location, self::LOCATIONS);

        $this->location = $location;
    }

    public function inHeaders() : bool
    {
        return $this->location === Input::LOCATION_HEADERS;
    }

    public function inPath() : bool
    {
        return $this->location === Input::LOCATION_PATH;
    }

    public function inQuery() : bool
    {
        return $this->location === Input::LOCATION_QUERY;
    }

    public function inBody() : bool
    {
        return $this->location === Input::LOCATION_BODY;
    }
}
