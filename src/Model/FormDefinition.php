<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use Speicher210\OpenApiGenerator\Assert\Assert;
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
        Assert::subclassOf($formClass, FormTypeInterface::class);

        $this->formClass        = $formClass;
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
}
