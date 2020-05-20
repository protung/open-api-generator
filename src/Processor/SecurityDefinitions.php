<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use cebe\openapi\spec\SecurityScheme;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SecurityDefinitions
{
    private OptionsResolver $optionResolver;

    public function __construct()
    {
        $this->optionResolver = new OptionsResolver();
        $this->configureOptions();
    }

    /**
     * @param mixed[] $config
     *
     * @return mixed[]
     */
    public function process(array $config): array
    {
        $definitions = [];
        foreach ($config as $name => $securityDefinition) {
            $definitions[$name] = new SecurityScheme($this->optionResolver->resolve($securityDefinition));
        }

        return $definitions;
    }

    private function configureOptions(): void
    {
        $this->optionResolver->setRequired('type');
        $this->optionResolver->setAllowedTypes('type', 'string');
        $this->optionResolver->setAllowedValues('type', ['http', 'apiKey', 'oauth2', 'openIdConnect']);

        $this->optionResolver->setDefined('in');
        $this->optionResolver->setAllowedTypes('in', ['null', 'string']);
        $this->optionResolver->setAllowedValues('in', [null, 'header', 'query']);

        $this->optionResolver->setDefined('name');
        $this->optionResolver->setAllowedTypes('name', ['null', 'string']);

        $this->optionResolver->setDefault('description', '');
        $this->optionResolver->setAllowedTypes('description', 'string');

        $this->optionResolver->setDefined('scheme');
        $this->optionResolver->setDefined('bearerFormat');
    }
}
