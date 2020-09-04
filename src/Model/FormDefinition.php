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

    /** @var string[] */
    private array $validationGroups;

    /**
     * @param class-string<FormTypeInterface> $formClass
     * @param string[]                        $validationGroups
     */
    public function __construct(string $formClass, array $validationGroups = [])
    {
        $this->formClass        = $formClass;
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
     * @return string[]
     */
    public function validationGroups(): array
    {
        return $this->validationGroups;
    }
}
