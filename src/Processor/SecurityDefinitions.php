<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use cebe\openapi\spec\SecurityScheme;
use Speicher210\OpenApiGenerator\Model\Security\Definition;
use function array_filter;

final class SecurityDefinitions
{
    /**
     * @param Definition[] $securityDefinitions
     *
     * @return array<string,SecurityScheme>
     */
    public function process(array $securityDefinitions) : array
    {
        $definitions = [];
        foreach ($securityDefinitions as $securityDefinition) {
            $definitions[$securityDefinition->key()] = new SecurityScheme(
                array_filter(
                    [
                        'type' => $securityDefinition->type(),
                        'description' => $securityDefinition->description(),
                        'name' => $securityDefinition->name(),
                        'in' => $securityDefinition->in(),
                        'scheme' => $securityDefinition->scheme(),
                        'bearerFormat' => $securityDefinition->bearerFormat(),
                    ]
                )
            );
        }

        return $definitions;
    }
}
