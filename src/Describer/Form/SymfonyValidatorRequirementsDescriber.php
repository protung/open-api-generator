<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form;

use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Describer\SpecificationDescriber;
use Psl;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Composite;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\DivisibleBy;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_map;
use function implode;
use function in_array;
use function number_format;

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

        $this->describeConstraints($constraints, $schema, $form);
    }

    /**
     * @return list<Constraint>
     */
    private function getConstraints(FormInterface $form): array
    {
        $formConfig = $form->getConfig();

        return Psl\Vec\concat(
            Psl\Type\vec(Psl\Type\instance_of(Constraint::class))->coerce($formConfig->getOption('constraints', [])),
            $this->getConstraintsForClass($formConfig),
            $this->getConstraintsForClassProperty($form),
        );
    }

    /**
     * @return array<Constraint>
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
     * @return list<Constraint>
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

        $propertyName = Psl\Type\string()->coerce($formConfig->getOption('property_path') ?? $form->getName());

        if ($parentMetadata->hasPropertyMetadata($propertyName)) {
            return Psl\Vec\flat_map(
                $parentMetadata->getPropertyMetadata($propertyName),
                static fn (PropertyMetadataInterface $propertyMetadata) => $propertyMetadata->getConstraints()
            );
        }

        return [];
    }

    /**
     * @param Constraint[] $constraints
     */
    private function handleNullability(Schema $schema, FormInterface $form, array $constraints): void
    {
        if ($form->isRoot()) {
            return;
        }

        $constraintClasses = array_map(static fn ($constraint) => $constraint::class, $constraints);

        if (in_array(NotNull::class, $constraintClasses, true) || in_array(NotBlank::class, $constraintClasses, true)) {
            return;
        }

        $schema->nullable = true;
    }

    private function describeComposite(Composite $constraint, Schema $schema, FormInterface $form): void
    {
        $this->describeConstraints(
            $constraint->getNestedConstraints(),
            $schema,
            $form,
        );
    }

    /**
     * @param array<Constraint> $constraints
     */
    private function describeConstraints(array $constraints, Schema $schema, FormInterface $form): void
    {
        $formIsCollection = $this->isCollection($form->getConfig()->getType());
        foreach ($constraints as $constraint) {
            switch (true) {
                case $constraint instanceof NotBlank:
                    if ($constraint->allowNull === true) {
                        $schema->nullable = true;
                    }

                    break;
                case $constraint instanceof NotNull:
                    // We handle nullability using $this->handleNullability method.
                    break;
                case $constraint instanceof Composite:
                    $this->describeComposite($constraint, $schema, $form);
                    break;
                case $constraint instanceof Count && $formIsCollection:
                    if ($constraint->min !== null) {
                        $schema->minItems = $constraint->min;
                    }

                    if ($constraint->max !== null) {
                        $schema->maxItems = $constraint->max;
                    }

                    break;
                case $constraint instanceof Count && ! $formIsCollection:
                    if ($constraint->min !== null) {
                        $schema->minProperties = $constraint->min;
                    }

                    if ($constraint->max !== null) {
                        $schema->maxProperties = $constraint->max;
                    }

                    break;
                case $constraint instanceof DivisibleBy:
                    $schema->multipleOf = $constraint->value;
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
                case $constraint instanceof Regex:
                    // we need to remove the delimiters but ignoring the modifiers
                    $schema->pattern = Psl\Str\slice(
                        Psl\Type\non_empty_string()->coerce(Psl\Str\before_last_ci($constraint->pattern, $constraint->pattern[0])),
                        1,
                    );
                    break;
                case $constraint instanceof File:
                    if ($constraint->mimeTypes !== null && $constraint->mimeTypes !== []) {
                        $schema->description = SpecificationDescriber::updateDescription(
                            $schema->description,
                            Psl\Str\format('Allowed mime types: %s', implode(', ', (array) $constraint->mimeTypes)),
                        );
                    }

                    if ($constraint->maxSize !== null) {
                        $schema->description = SpecificationDescriber::updateDescription(
                            $schema->description,
                            Psl\Str\format('Allowed max file size: %s', $this->humanReadableFileSize($constraint->maxSize)),
                        );
                    }

                    if ($constraint instanceof Image) {
                        if ($constraint->minWidth !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed minimum width is %dpx', $constraint->minWidth),
                            );
                        }

                        if ($constraint->minHeight !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed minimum height is %dpx', $constraint->minHeight),
                            );
                        }

                        if ($constraint->maxWidth !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed maximum width is %dpx', $constraint->maxWidth),
                            );
                        }

                        if ($constraint->maxHeight !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed maximum height is %dpx', $constraint->maxHeight),
                            );
                        }
                    }

                    break;
            }
        }
    }

    private function humanReadableFileSize(int $size): string
    {
        if ($size >= 1048576) {
            return Psl\Str\format('%s MB', number_format($size / 1048576, $size % 1048576 === 0 ? 0 : 3));
        }

        if ($size >= 1024) {
            return Psl\Str\format('%s KB', number_format($size / 1024, $size % 1024 === 0 ? 0 : 3));
        }

        return Psl\Str\format('%d bytes', $size);
    }

    private function isCollection(ResolvedFormTypeInterface $formType): bool
    {
        if ($formType->getBlockPrefix() === 'collection') {
            return true;
        }

        $parentType = $formType->getParent();
        if ($parentType !== null) {
            return $this->isCollection($parentType);
        }

        return false;
    }
}
