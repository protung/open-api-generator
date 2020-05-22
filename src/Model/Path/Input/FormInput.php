<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Input;

use Speicher210\OpenApiGenerator\Model\FormDefinition;

final class FormInput extends BaseInput
{
    private FormDefinition $formDefinition;

    public function __construct(FormDefinition $formDefinition, string $locations)
    {
        $this->formDefinition = $formDefinition;
        $this->setLocation($locations);
    }

    public function formDefinition() : FormDefinition
    {
        return $this->formDefinition;
    }
}
