<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Input;

use Speicher210\OpenApiGenerator\Model\FormDefinition;
use Speicher210\OpenApiGenerator\Model\Path\Input;

final class FormInput extends BaseInput
{
    private FormDefinition $formDefinition;

    private function __construct(FormDefinition $formDefinition, string $location)
    {
        $this->formDefinition = $formDefinition;
        $this->setLocation($location);
    }

    public static function inBody(FormDefinition $formDefinition) : self
    {
        return new self($formDefinition, Input::LOCATION_BODY);
    }

    public static function inQuery(FormDefinition $formDefinition) : self
    {
        return new self($formDefinition, Input::LOCATION_QUERY);
    }

    public function formDefinition() : FormDefinition
    {
        return $this->formDefinition;
    }
}
