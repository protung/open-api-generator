<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form;

use cebe\openapi\spec\Schema;
use Override;
use Protung\OpenApiGenerator\Describer\SymfonyValidatorConstraintsDescriber;
use Psl;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function array_map;
use function in_array;

final class SymfonyValidatorRequirementsDescriber implements RequirementsDescriber
{
    private ValidatorInterface $validator;

    private SymfonyValidatorConstraintsDescriber $constraintsDescriber;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator            = $validator;
        $this->constraintsDescriber = new SymfonyValidatorConstraintsDescriber();
    }

    /** @param FormInterface<mixed> $form */
    #[Override]
    public function describe(Schema $schema, FormInterface $form): void
    {
        $constraints = $this->getConstraints($form);

        $this->handleNullability($schema, $form, $constraints);

        $this->constraintsDescriber->describe(
            $constraints,
            $schema,
            $this->isCollection($form->getConfig()->getType()),
        );
    }

    /**
     * @param FormInterface<mixed> $form
     *
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
     * @param FormConfigInterface<mixed> $formConfig
     *
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
     * @param FormInterface<mixed> $form
     *
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
                static fn (PropertyMetadataInterface $propertyMetadata): array => $propertyMetadata->getConstraints(),
            );
        }

        return [];
    }

    /**
     * @param FormInterface<mixed> $form
     * @param Constraint[]         $constraints
     */
    private function handleNullability(Schema $schema, FormInterface $form, array $constraints): void
    {
        if ($form->isRoot()) {
            return;
        }

        $constraintClasses = array_map(static fn (Constraint $constraint): string => $constraint::class, $constraints);

        if (in_array(NotNull::class, $constraintClasses, true) || in_array(NotBlank::class, $constraintClasses, true)) {
            return;
        }

        $schema->nullable = true;
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
