<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form\PropertyDescriber;

use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Describer\FormDescriber;
use Symfony\Component\Form\FormInterface;

interface PropertyDescriber
{
    public function describe(Schema $schema, FormInterface $form, FormDescriber $formDescriber): void;

    public function supports(FormInterface $form): bool;
}
