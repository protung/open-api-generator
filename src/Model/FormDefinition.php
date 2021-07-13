<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use Symfony\Component\Form\FormTypeInterface;

/**
 * @psalm-immutable
 */
final class FormDefinition
{
    /** @var class-string<FormTypeInterface> */
    private string $formClass;

    /** @var array<mixed> */
    private array $formOptions;

    /** @var array<string> */
    private array $validationGroups;

    /**
     * @param class-string<FormTypeInterface> $formClass
     * @param array<mixed>                    $formOptions
     * @param array<string>                   $validationGroups
     */
    public function __construct(string $formClass, array $formOptions = [], array $validationGroups = [])
    {
        $this->formClass        = $formClass;
        $this->formOptions      = $formOptions;
        $this->validationGroups = $validationGroups;
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    public function formClass(): string
    {
        return $this->formClass;
    }

    /**
     * @return array<mixed>
     */
    public function formOptions(): array
    {
        return $this->formOptions;
    }

    /**
     * @return array<string>
     */
    public function validationGroups(): array
    {
        return $this->validationGroups;
    }
}
