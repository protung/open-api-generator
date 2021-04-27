<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Describer\Form\FlatNameResolver;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Describer\Form\NameResolver;
use Speicher210\OpenApiGenerator\Describer\Form\RequirementsDescriber;
use Speicher210\OpenApiGenerator\Describer\Form\SymfonyFormPropertyDescriber;
use Speicher210\OpenApiGenerator\Model\FormDefinition;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

use function get_class;
use function sprintf;
use function strpos;

final class FormDescriber
{
    private FormFactory $formFactory;

    private SymfonyFormPropertyDescriber $propertyDescriber;

    private RequirementsDescriber $requirementsDescriber;

    public function __construct(
        FormFactory $formFactory,
        SymfonyFormPropertyDescriber $propertyDescriber,
        RequirementsDescriber $requirementsDescriber
    ) {
        $this->formFactory           = $formFactory;
        $this->propertyDescriber     = $propertyDescriber;
        $this->requirementsDescriber = $requirementsDescriber;
    }

    public function addDeepSchema(FormInterface $form, NameResolver $nameResolver): Schema
    {
        $schema = $this->createSchema($form);
        $this->handleRequiredForParent($schema, $form, $nameResolver);
        foreach ($form->all() as $child) {
            $type = $child->getConfig()->getType();

            if ($this->isBuiltinType($type->getInnerType())) {
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
                $childConfig = $child->getConfig();
                $childType   = $childConfig->getType();

                if (! $this->isBuiltinType($childType->getInnerType())) {
                    $this->addParametersToFlattenSchema($schema, $child, $nameResolver);
                } elseif ($childType->getBlockPrefix() === 'collection') {
                    $subForm = $this->formFactory->create(
                        new FormDefinition(
                            $childConfig->getOption('entry_type'),
                            (array) $childConfig->getOption('validation_groups')
                        ),
                        $form->getRoot()->getConfig()->getMethod()
                    );

                    // Primitive type so we add array normally.
                    if ($subForm->count() === 0) {
                        $this->addParameterToSchema($schema, $nameResolver, $child);
                    } else {
                        $prefix = $nameResolver->getPropertyName($child);

                        $this->addParametersToFlattenSchema(
                            $schema,
                            $subForm,
                            new NameResolver\PrefixedFlatArray($prefix)
                        );
                    }
                } else {
                    $this->addParameterToSchema($schema, $nameResolver, $child);
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
        $formConfig  = $form->getConfig();
        $blockPrefix = $formConfig->getType()->getBlockPrefix();

        $schema = new Schema([]);

        $this->propertyDescriber->describe($schema, $blockPrefix, $form, $this);
        $this->requirementsDescriber->describe($schema, $form);

        return $schema;
    }

    /**
     * @psalm-pure
     */
    private function isBuiltinType(FormTypeInterface $formType): bool
    {
        $formClass = get_class($formType);

        return strpos($formClass, 'Symfony\Component\Form\Extension\Core\Type') === 0;
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
