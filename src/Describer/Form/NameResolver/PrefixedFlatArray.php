<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form\NameResolver;

use Protung\OpenApiGenerator\Describer\Form\FlatNameResolver;
use Protung\OpenApiGenerator\Describer\Form\NameResolver\FlatNameResolver as FlatNameResolverTrait;
use Symfony\Component\Form\FormInterface;

final class PrefixedFlatArray implements FlatNameResolver
{
    use FlatNameResolverTrait;

    private string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function getPropertyName(FormInterface $form): string
    {
        $names = $this->namesFromForm($form);

        $name = $this->prefix;

        return $this->fromArray($name, $names, $form->getConfig());
    }
}
