<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form\NameResolver;

use Override;
use Protung\OpenApiGenerator\Describer\Form\NameResolver;
use Symfony\Component\Form\FormInterface;

final class FormName implements NameResolver
{
    #[Override]
    public function getPropertyName(FormInterface $form): string
    {
        return $form->getName();
    }
}
