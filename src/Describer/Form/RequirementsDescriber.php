<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form;

use cebe\openapi\spec\Schema;
use Symfony\Component\Form\FormInterface;

interface RequirementsDescriber
{
    /** @param FormInterface<mixed> $form */
    public function describe(Schema $schema, FormInterface $form): void;
}
