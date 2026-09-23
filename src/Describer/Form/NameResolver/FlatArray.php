<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form\NameResolver;

use Override;
use Psl;
use Symfony\Component\Form\FormInterface;

use function array_shift;

final class FlatArray implements \Protung\OpenApiGenerator\Describer\Form\FlatNameResolver
{
    use FlatNameResolver;

    #[Override]
    public function getPropertyName(FormInterface $form): string
    {
        $names = $this->namesFromForm($form);

        $name = Psl\Type\string()->coerce(array_shift($names));

        return $this->fromArray($name, $names, $form->getConfig());
    }
}
