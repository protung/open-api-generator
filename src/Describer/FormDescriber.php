<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Protung\OpenApiGenerator\Describer\Form\FlatNameResolver;
use Protung\OpenApiGenerator\Describer\Form\NameResolver;
use Protung\OpenApiGenerator\Describer\Form\RequirementsDescriber;
use Protung\OpenApiGenerator\Describer\Form\SymfonyFormPropertyDescriber;
use Psl;
use Symfony\Component\Form\FormInterface;

final class FormDescriber
{
    private SymfonyFormPropertyDescriber $propertyDescriber;

    private RequirementsDescriber $requirementsDescriber;

    public function __construct(
        SymfonyFormPropertyDescriber $propertyDescriber,
        RequirementsDescriber $requirementsDescriber,
    ) {
        $this->propertyDescriber     = $propertyDescriber;
        $this->requirementsDescriber = $requirementsDescriber;
    }

    /** @param FormInterface<mixed> $form */
    public function addDeepSchema(FormInterface $form, NameResolver $nameResolver): Schema
    {
        $schema = $this->createSchema($form);

        return $this->addParametersToDeepSchema($schema, $form, $nameResolver);
    }

    /** @param FormInterface<mixed> $form */
    public function addParametersToDeepSchema(Schema $schema, FormInterface $form, NameResolver $nameResolver): Schema
    {
        if ($form->count() === 0) {
            $this->describeProperty($schema, $form);
        } else {
            $schema->type = Type::OBJECT;

            foreach ($form->all() as $child) {
                if ($child->count() === 0) {
                    $this->addParameterToSchema($schema, $nameResolver, $child);
                } else {
                    $childSchema = $this->addDeepSchema($child, $nameResolver);

                    $name                    = $nameResolver->getPropertyName($child);
                    $schemaProperties        = $schema->properties;
                    $schemaProperties[$name] = $childSchema;
                    $schema->properties      = $schemaProperties;

                    $this->handleRequiredProperty($schema, $child, $nameResolver);
                }
            }

            if ($schema->required === []) {
                unset($schema->required);
            }
        }

        $this->handleRequiredForParent($schema, $form, $nameResolver);

        return $schema;
    }

    /** @param FormInterface<mixed> $form */
    public function addFlattenSchema(FormInterface $form, FlatNameResolver $nameResolver): Schema
    {
        $schema = new Schema(['type' => Type::OBJECT]);

        return $this->addParametersToFlattenSchema($schema, $form, $nameResolver);
    }

    /** @param FormInterface<mixed> $form */
    private function addParametersToFlattenSchema(
        Schema $schema,
        FormInterface $form,
        FlatNameResolver $nameResolver,
    ): Schema {
        if ($form->count() === 0) {
            $this->addParameterToSchema($schema, $nameResolver, $form);
        } else {
            foreach ($form->all() as $child) {
                if ($child->count() === 0) {
                    $this->addParameterToSchema($schema, $nameResolver, $child);
                } else {
                    $this->addParametersToFlattenSchema($schema, $child, $nameResolver);
                }
            }
        }

        if ($schema->required === []) {
            unset($schema->required);
        }

        return $schema;
    }

    /** @param FormInterface<mixed> $form */
    private function addParameterToSchema(
        Schema $schema,
        NameResolver $nameResolver,
        FormInterface $form,
    ): void {
        $childSchema = $this->createSchema($form);
        $this->handleRequiredForParent($childSchema, $form, $nameResolver);

        $name                    = $nameResolver->getPropertyName($form);
        $schemaProperties        = $schema->properties;
        $schemaProperties[$name] = $childSchema;
        $schema->properties      = $schemaProperties;

        $this->handleRequiredProperty($schema, $form, $nameResolver);
    }

    /** @param FormInterface<mixed> $form */
    private function createSchema(FormInterface $form): Schema
    {
        $schema = new Schema([]);

        $this->describeProperty($schema, $form);

        return $schema;
    }

    /** @param FormInterface<mixed> $form */
    private function describeProperty(Schema $schema, FormInterface $form): void
    {
        $this->propertyDescriber->describe($schema, $form, $this);
        $this->requirementsDescriber->describe($schema, $form);
    }

    /** @param FormInterface<mixed> $form */
    private function handleRequiredForParent(Schema $schema, FormInterface $form, NameResolver $nameResolver): void
    {
        if (! $nameResolver instanceof FlatNameResolver) {
            return;
        }

        if ($form->getConfig()->getRequired() !== true) {
            return;
        }

        $parentForm = $form->getParent();
        if ($parentForm === null || $parentForm->isRoot() || $parentForm->isRequired()) {
            return;
        }

        $schema->description = SpecificationDescriber::updateDescription(
            $schema->description,
            Psl\Str\format('Field required for %s', $nameResolver->getPropertyName($parentForm)),
        );
    }

    /** @param FormInterface<mixed> $form */
    private function handleRequiredProperty(Schema $schema, FormInterface $form, NameResolver $nameResolver): void
    {
        if (! $this->isFormPropertyRequired($form, $nameResolver)) {
            return;
        }

        $schemaRequired   = $schema->required;
        $schemaRequired[] = $nameResolver->getPropertyName($form);
        $schema->required = $schemaRequired;
    }

    /** @param FormInterface<mixed> $form */
    private function isFormPropertyRequired(FormInterface $form, NameResolver $nameResolver): bool
    {
        $httpMethod = $form->getRoot()->getConfig()->getMethod();

        // For PATCH endpoints all properties are optional.
        if ($httpMethod === 'PATCH') {
            return false;
        }

        if (! $form->getConfig()->getRequired()) {
            return false;
        }

        if ($nameResolver instanceof FlatNameResolver) {
            $parentForm = $form->getParent();

            return ! ($parentForm !== null && ! $parentForm->isRoot() && ! $parentForm->isRequired());
        }

        return true;
    }
}
