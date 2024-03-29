<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form\NameResolver;

use Protung\OpenApiGenerator\Describer\Form\NameResolver;
use Symfony\Component\Form\FormInterface;

final class FormName implements NameResolver
{
    public function getPropertyName(FormInterface $form): string
    {
        return $form->getName();
    }
}
