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

    /** @var array<string, mixed> */
    private array $formOptions;

    /**
     * @param class-string<FormTypeInterface> $formClass
     * @param array<string, mixed>            $formOptions
     */
    public function __construct(string $formClass, array $formOptions = [])
    {
        $this->formClass   = $formClass;
        $this->formOptions = $formOptions;
    }

    /**
     * @return class-string<FormTypeInterface>
     */
    public function formClass(): string
    {
        return $this->formClass;
    }

    /**
     * @return array<string, mixed>
     */
    public function formOptions(): array
    {
        return $this->formOptions;
    }
}
