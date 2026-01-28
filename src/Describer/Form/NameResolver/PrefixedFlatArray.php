<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form\NameResolver;

use Override;
use Symfony\Component\Form\FormInterface;

final class PrefixedFlatArray implements \Protung\OpenApiGenerator\Describer\Form\FlatNameResolver
{
    use FlatNameResolver;

    private string $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    #[Override]
    public function getPropertyName(FormInterface $form): string
    {
        $names = $this->namesFromForm($form);

        $name = $this->prefix;

        return $this->fromArray($name, $names, $form->getConfig());
    }
}
