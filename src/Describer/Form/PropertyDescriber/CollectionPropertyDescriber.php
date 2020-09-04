<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form\PropertyDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Describer\Form\NameResolver\FormName;
use Speicher210\OpenApiGenerator\Describer\FormDescriber;
use Speicher210\OpenApiGenerator\Model\FormDefinition;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;

use function get_class;

final class CollectionPropertyDescriber implements PropertyDescriber
{
    private FormFactory $formFactory;

    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function describe(Schema $schema, FormInterface $form, FormDescriber $formDescriber): void
    {
        $formConfig = $form->getConfig();

        $subForm = $this->formFactory->create(
            new FormDefinition(
                $formConfig->getOption('entry_type'),
                (array) $formConfig->getOption('validation_groups')
            )
        );

        $schema->type  = Type::ARRAY;
        $schema->items = $formDescriber->addDeepSchema($subForm, new FormName(), $formConfig->getMethod());
    }

    public function supports(FormInterface $form): bool
    {
        return $this->isCollection($form->getConfig());
    }

    private function isCollection(FormConfigInterface $formConfig): bool
    {
        if ($formConfig->getType()->getBlockPrefix() === 'collection') {
            return true;
        }

        $parentType = $formConfig->getType()->getParent();
        if ($parentType !== null) {
            $newForm = $this->formFactory->create(
                new FormDefinition(
                    get_class($parentType->getInnerType()),
                    (array) $formConfig->getOption('validation_groups')
                )
            );

            return $this->isCollection($newForm->getConfig());
        }

        return false;
    }
}
