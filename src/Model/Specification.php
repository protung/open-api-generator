<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use Speicher210\OpenApiGenerator\Model\Info\Info;
use Speicher210\OpenApiGenerator\Model\Path\Path;

final class Specification
{
    private Info $info;

    /** @var Security\Definition[] */
    private array $securityDefinitions;

    /** @var Path[] */
    private array $paths;

    /**
     * @param Security\Definition[] $securityDefinitions
     * @param Path[]                $paths
     */
    public function __construct(Info $info, array $securityDefinitions, array $paths)
    {
        $this->info                = $info;
        $this->securityDefinitions = $securityDefinitions;
        $this->paths               = $paths;
    }

    public function info() : Info
    {
        return $this->info;
    }

    /**
     * @return Security\Definition[]
     */
    public function securityDefinitions() : array
    {
        return $this->securityDefinitions;
    }

    /**
     * @return Path[]
     */
    public function paths() : array
    {
        return $this->paths;
    }
}
