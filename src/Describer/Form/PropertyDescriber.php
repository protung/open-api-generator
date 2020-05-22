<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form;

use cebe\openapi\spec\Schema;
use Symfony\Component\Form\FormInterface;

interface PropertyDescriber
{
    public function describe(Schema $schema, string $blockPrefix, FormInterface $form) : void;
}
