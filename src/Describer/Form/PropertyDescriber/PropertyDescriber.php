<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form\PropertyDescriber;

use cebe\openapi\spec\Schema;
use Speicher210\OpenApiGenerator\Describer\FormDescriber;
use Symfony\Component\Form\FormInterface;

interface PropertyDescriber
{
    public function describe(Schema $schema, FormInterface $form, FormDescriber $formDescriber): void;

    public function supports(FormInterface $form): bool;
}
