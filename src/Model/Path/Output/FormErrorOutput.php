<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Assert\Assertion;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Symfony\Component\Form\FormTypeInterface;

final class FormErrorOutput implements Output
{
    private string $formClass;

    /** @var string[] */
    private array $validationGroups;

    /**
     * @param class-string $formType
     */
    public function __construct(string $formClass, array $validationGroups = [])
    {
        Assertion::implementsInterface($formClass, FormTypeInterface::class);

        $this->formClass = $formClass;
        $this->validationGroups = $validationGroups;
    }

    public function formClass() : string
    {
        return $this->formClass;
    }

    /**
     * @return string[]
     */
    public function validationGroups() : array
    {
        return $this->validationGroups;
    }

    public function example()
    {
        // TODO: Implement example() method.
    }
}
