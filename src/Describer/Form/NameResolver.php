<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form;

use Symfony\Component\Form\FormInterface;

interface NameResolver
{
    /** @param FormInterface<mixed> $form */
    public function getPropertyName(FormInterface $form): string;
}
