<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Input;

use Override;
use Protung\OpenApiGenerator\Model\Path\Input;
use Protung\OpenApiGenerator\Model\Path\InputLocation;

abstract class BaseInput implements Input
{
    private InputLocation $location;

    final protected function setLocation(InputLocation $location): void
    {
        $this->location = $location;
    }

    #[Override]
    public function location(): InputLocation
    {
        return $this->location;
    }

    #[Override]
    public function isInHeaders(): bool
    {
        return $this->location === InputLocation::Header;
    }

    #[Override]
    public function isInPath(): bool
    {
        return $this->location === InputLocation::Path;
    }

    #[Override]
    public function isInQuery(): bool
    {
        return $this->location === InputLocation::Query;
    }

    #[Override]
    public function isInBody(): bool
    {
        return $this->location === InputLocation::Body;
    }
}
