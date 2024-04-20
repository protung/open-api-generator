<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Processor;

use cebe\openapi\spec\Components;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\SecurityScheme;
use Protung\OpenApiGenerator\Model\Specification;

use function array_filter;
use function count;

final class SecurityDefinitions implements Processor
{
    public function process(OpenApi $openApi, Specification $specification): void
    {
        $definitions = [];
        foreach ($specification->securityDefinitions() as $securityDefinition) {
            $definitions[$securityDefinition->key()] = new SecurityScheme(
                array_filter(
                    [
                        'type' => $securityDefinition->type(),
                        'description' => $securityDefinition->description(),
                        'name' => $securityDefinition->name(),
                        'in' => $securityDefinition->in(),
                        'scheme' => $securityDefinition->scheme(),
                        'bearerFormat' => $securityDefinition->bearerFormat(),
                    ],
                    static fn (mixed $value): bool => $value !== null && $value !== '',
                ),
            );
        }

        if ($openApi->components === null) {
            $openApi->components = new Components([]);
        }

        if (count($definitions) === 0) {
            return;
        }

        $openApi->components->securitySchemes = $definitions;
    }
}
