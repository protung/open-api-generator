<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model\Path\Input;

use Protung\OpenApiGenerator\Model\FormDefinition;
use Protung\OpenApiGenerator\Model\Path\InputLocation;

final class FormInput extends BaseInput
{
    private FormDefinition $formDefinition;

    private function __construct(FormDefinition $formDefinition, InputLocation $location)
    {
        $this->formDefinition = $formDefinition;
        $this->setLocation($location);
    }

    public static function inBody(FormDefinition $formDefinition): self
    {
        return new self($formDefinition, InputLocation::Body);
    }

    public static function inQuery(FormDefinition $formDefinition): self
    {
        return new self($formDefinition, InputLocation::Query);
    }

    public function formDefinition(): FormDefinition
    {
        return $this->formDefinition;
    }
}
