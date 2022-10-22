<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model;

use Protung\OpenApiGenerator\Model\Info\Info;
use Protung\OpenApiGenerator\Model\Path\Input;
use Protung\OpenApiGenerator\Model\Path\Path;

final class Specification
{
    private Info $info;

    /** @var Security\Definition[] */
    private array $securityDefinitions;

    /** @var Path[] */
    private array $paths;

    /** @var Input[] */
    private array $alwaysAddedInputs;

    /** @var Response[] */
    private array $alwaysAddedResponses;

    /**
     * @param Security\Definition[] $securityDefinitions
     * @param Path[]                $paths
     * @param Input[]               $alwaysAddedInputs
     * @param Response[]            $alwaysAddedResponses
     */
    public function __construct(
        Info $info,
        array $securityDefinitions,
        array $paths,
        array $alwaysAddedInputs = [],
        array $alwaysAddedResponses = [],
    ) {
        $this->info                 = $info;
        $this->securityDefinitions  = $securityDefinitions;
        $this->paths                = $paths;
        $this->alwaysAddedInputs    = $alwaysAddedInputs;
        $this->alwaysAddedResponses = $alwaysAddedResponses;
    }

    public function info(): Info
    {
        return $this->info;
    }

    /**
     * @return Security\Definition[]
     */
    public function securityDefinitions(): array
    {
        return $this->securityDefinitions;
    }

    /**
     * @return Path[]
     */
    public function paths(): array
    {
        return $this->paths;
    }

    /**
     * @return Input[]
     */
    public function alwaysAddedInputs(): array
    {
        return $this->alwaysAddedInputs;
    }

    /**
     * @return Response[]
     */
    public function alwaysAddedResponses(): array
    {
        return $this->alwaysAddedResponses;
    }
}
