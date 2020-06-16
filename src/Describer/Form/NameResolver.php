<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form;

use Symfony\Component\Form\FormInterface;

interface NameResolver
{
    public function getPropertyName(FormInterface $form): string;
}
