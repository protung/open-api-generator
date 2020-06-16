<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form;

use cebe\openapi\spec\Schema;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_map;
use function array_merge;
use function get_class;
use function in_array;

final class SymfonyValidatorRequirementsDescriber implements RequirementsDescriber
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function describe(Schema $schema, FormInterface $form): void
    {
        $constraints = $this->getConstraints($form);

        $this->handleNullability($schema, $form, $constraints);

        foreach ($constraints as $constraint) {
            switch (true) {
                case $constraint instanceof Count:
                    if ($constraint->min !== null) {
                        $schema->minItems = $constraint->min;
                    }

                    if ($constraint->max !== null) {
                        $schema->maxItems = $constraint->max;
                    }

                    break;
                case $constraint instanceof GreaterThan:
                    $schema->minimum          = $constraint->value;
                    $schema->exclusiveMinimum = true;
                    break;
                case $constraint instanceof GreaterThanOrEqual:
                    $schema->minimum = $constraint->value;
                    break;
                case $constraint instanceof Length:
                    if ($constraint->min !== null) {
                        $schema->minLength = $constraint->min;
                    }

                    if ($constraint->max !== null) {
                        $schema->maxLength = $constraint->max;
                    }

                    break;
                case $constraint instanceof LessThan:
                    $schema->maximum          = $constraint->value;
                    $schema->exclusiveMaximum = true;
                    break;
                case $constraint instanceof LessThanOrEqual:
                    $schema->maximum = $constraint->value;
                    break;
                case $constraint instanceof Range:
                    if ($constraint->min !== null) {
                        $schema->minimum = $constraint->min;
                    }

                    if ($constraint->max !== null) {
                        $schema->maximum = $constraint->max;
                    }

                    break;
                case $constraint instanceof Unique:
                    $schema->uniqueItems = true;
                    break;
            }
        }
    }

    /**
     * @return Constraint[]
     */
    private function getConstraints(FormInterface $form): array
    {
        $formConfig = $form->getConfig();

        return array_merge(
            $formConfig->getOption('constraints', []),
            $this->getConstraintsForClass($formConfig),
            $this->getConstraintsForClassProperty($form)
        );
    }

    /**
     * @return Constraint[]
     */
    private function getConstraintsForClass(FormConfigInterface $formConfig): array
    {
        $class = $formConfig->getOption('data_class');
        if ($class === null) {
            return [];
        }

        return $this->validator->getMetadataFor($class)->getConstraints();
    }

    /**
     * @return Constraint[]
     */
    private function getConstraintsForClassProperty(FormInterface $form): array
    {
        $formConfig = $form->getConfig();
        if ($formConfig->getOption('mapped') === false) {
            return [];
        }

        $parentForm = $form->getParent();
        if ($parentForm === null) {
            return [];
        }

        $parentClass = $parentForm->getConfig()->getOption('data_class');
        if ($parentClass === null) {
            return [];
        }

        $parentMetadata = $this->validator->getMetadataFor($parentClass);
        if (! $parentMetadata instanceof ClassMetadataInterface) {
            return [];
        }

        $propertyName = $formConfig->getOption('property_path') ?? $form->getName();

        if ($parentMetadata->hasPropertyMetadata($propertyName)) {
            $propertyConstraints = [];
            foreach ($parentMetadata->getPropertyMetadata($propertyName) as $propertyMetadata) {
                $propertyConstraints[] = $propertyMetadata->getConstraints();
            }

            $propertyConstraints = array_merge(...$propertyConstraints);

            return $propertyConstraints;
        }

        return [];
    }

    private function isDescribingClass(FormInterface $form): bool
    {
        $formConfig = $form->getConfig();
        if ($formConfig->getOption('data_class') !== null) {
            return true;
        }

        $parent = $form->getParent();
        if ($parent !== null) {
            return $this->isDescribingClass($parent);
        }

        return false;
    }

    /**
     * @param Constraint[] $constraints
     */
    private function handleNullability(Schema $schema, FormInterface $form, array $constraints): void
    {
        if (! $this->isDescribingClass($form)) {
            return;
        }

        $constraintClasses = array_map(static fn ($constraint) => get_class($constraint), $constraints);

        if (in_array(NotNull::class, $constraintClasses, true) || in_array(NotBlank::class, $constraintClasses, true)) {
            return;
        }

        $schema->nullable = true;
    }
}
