<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class Info
{
    private OptionsResolver $optionResolver;

    public function __construct(string $apiVersion)
    {
        $this->optionResolver = new OptionsResolver();
        $this->configureOptions($apiVersion);
    }

    /**
     * @param mixed[] $config
     *
     * @return mixed[]
     */
    public function process(array $config): array
    {
        return $this->optionResolver->resolve($config);
    }

    private function configureOptions(string $apiVersion): void
    {
        $this->optionResolver->setRequired('title');
        $this->optionResolver->setAllowedTypes('title', 'string');

        $this->optionResolver->setDefined('description');
        $this->optionResolver->setAllowedTypes('description', ['string', 'null']);

        $this->optionResolver->setDefault('version', $apiVersion);
        $this->optionResolver->setAllowedTypes('version', 'string');
    }
}
