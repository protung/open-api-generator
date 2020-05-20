<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use Assert\Assertion;
use Symfony\Component\Form\FormTypeInterface;

final class FormDefinition
{
    private string $formClass;

    /** @var string[] */
    private array $validationGroups;

    /**
     * @param string[] $validationGroups
     */
    public function __construct(string $formClass, array $validationGroups = [])
    {
        Assertion::implementsInterface($formClass, FormTypeInterface::class);

        $this->formClass = $formClass;
        $this->validationGroups = $validationGroups;
    }

    public function formClass(): string
    {
        return $this->formClass;
    }

    /**
     * @return string[]
     */
    public function validationGroups(): array
    {
        return $this->validationGroups;
    }

    public function hasValidationGroups(): bool
    {
        return \count($this->validationGroups) > 0;
    }
}
