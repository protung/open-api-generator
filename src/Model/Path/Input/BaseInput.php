<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Input;

use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Model\Path\Input;

abstract class BaseInput implements Input
{
    private string $location;

    final protected function setLocation(string $location): void
    {
        Assert::inArray($location, self::LOCATIONS);

        $this->location = $location;
    }

    public function location(): string
    {
        return $this->location;
    }

    public function isInHeaders(): bool
    {
        return $this->location === Input::LOCATION_HEADERS;
    }

    public function isInPath(): bool
    {
        return $this->location === Input::LOCATION_PATH;
    }

    public function isInQuery(): bool
    {
        return $this->location === Input::LOCATION_QUERY;
    }

    public function isInBody(): bool
    {
        return $this->location === Input::LOCATION_BODY;
    }
}
