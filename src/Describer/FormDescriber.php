<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Describer\Form\FlatNameResolver;
use Speicher210\OpenApiGenerator\Describer\Form\NameResolver;
use Speicher210\OpenApiGenerator\Describer\Form\RequirementsDescriber;
use Speicher210\OpenApiGenerator\Describer\Form\SymfonyFormPropertyDescriber;
use Symfony\Component\Form\FormInterface;

use function sprintf;

final class FormDescriber
{
    private SymfonyFormPropertyDescriber $propertyDescriber;

    private RequirementsDescriber $requirementsDescriber;

    public function __construct(
        SymfonyFormPropertyDescriber $propertyDescriber,
        RequirementsDescriber $requirementsDescriber
    ) {
        $this->propertyDescriber     = $propertyDescriber;
        $this->requirementsDescriber = $requirementsDescriber;
    }

    public function addDeepSchema(FormInterface $form, NameResolver $nameResolver): Schema
    {
        $schema = $this->createSchema($form);
        $this->handleRequiredForParent($schema, $form, $nameResolver);
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

        return $schema;
    }

    public function addFlattenSchema(FormInterface $form, FlatNameResolver $nameResolver): Schema
    {
        $schema = new Schema(['type' => Type::OBJECT]);

        return $this->addParametersToFlattenSchema($schema, $form, $nameResolver);
    }

    private function addParametersToFlattenSchema(
        Schema $schema,
        FormInterface $form,
        FlatNameResolver $nameResolver
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

    private function addParameterToSchema(
        Schema $schema,
        NameResolver $nameResolver,
        FormInterface $form
    ): void {
        $childSchema = $this->createSchema($form);
        $this->handleRequiredForParent($childSchema, $form, $nameResolver);

        $name                    = $nameResolver->getPropertyName($form);
        $schemaProperties        = $schema->properties;
        $schemaProperties[$name] = $childSchema;
        $schema->properties      = $schemaProperties;

        $this->handleRequiredProperty($schema, $form, $nameResolver);
    }

    private function createSchema(FormInterface $form): Schema
    {
        $schema = new Schema([]);

        $this->propertyDescriber->describe($schema, $form, $this);
        $this->requirementsDescriber->describe($schema, $form);

        return $schema;
    }

    private function handleRequiredForParent(Schema $schema, FormInterface $form, NameResolver $nameResolver): void
    {
        if (! $nameResolver instanceof FlatNameResolver) {
            return;
        }

        if ($form->getConfig()->getRequired() !== true) {
            return;
        }

        $parentForm = $form->getParent();
        if ($parentForm === null || $parentForm->isRoot() || $parentForm->isRequired() !== false) {
            return;
        }

        $schema->description = SpecificationDescriber::updateDescription(
            $schema->description,
            sprintf('Field required for %s', $nameResolver->getPropertyName($parentForm))
        );
    }

    private function handleRequiredProperty(Schema $schema, FormInterface $form, NameResolver $nameResolver): void
    {
        if ($this->isFormPropertyRequired($form, $nameResolver) !== true) {
            return;
        }

        $schemaRequired   = $schema->required;
        $schemaRequired[] = $nameResolver->getPropertyName($form);
        $schema->required = $schemaRequired;
    }

    private function isFormPropertyRequired(FormInterface $form, NameResolver $nameResolver): bool
    {
        $httpMethod = $form->getRoot()->getConfig()->getMethod();

        // For PATCH endpoints all properties are optional.
        if ($httpMethod === 'PATCH') {
            return false;
        }

        if ($form->getConfig()->getRequired() === false) {
            return false;
        }

        if ($nameResolver instanceof FlatNameResolver) {
            $parentForm = $form->getParent();

            return ! ($parentForm !== null && ! $parentForm->isRoot() && $parentForm->isRequired() === false);
        }

        return true;
    }
}
