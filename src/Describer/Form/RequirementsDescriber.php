<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form;

use cebe\openapi\spec\Schema;
use Symfony\Component\Form\FormInterface;

interface RequirementsDescriber
{
    public function describe(Schema $schema, FormInterface $form) : void;
}
