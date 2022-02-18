<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form\PropertyDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Psl;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Describer\Form\FormFactory;
use Speicher210\OpenApiGenerator\Describer\Form\NameResolver\FormName;
use Speicher210\OpenApiGenerator\Describer\FormDescriber;
use Speicher210\OpenApiGenerator\Model\FormDefinition;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

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

        $entryType = Psl\Type\string()->coerce($formConfig->getOption('entry_type'));
        Assert::implementsInterface($entryType, FormTypeInterface::class);

        $subForm = $this->formFactory->create(
            new FormDefinition(
                $entryType,
                Psl\Dict\merge(
                    [
                        'validation_groups' => (array) $formConfig->getOption('validation_groups'),
                    ],
                    Psl\Type\dict(Psl\Type\string(), Psl\Type\mixed())->coerce($formConfig->getOption('entry_options'))
                )
            ),
            $form->getRoot()->getConfig()->getMethod()
        );

        $schema->type  = Type::ARRAY;
        $schema->items = $formDescriber->addDeepSchema($subForm, new FormName());
    }

    public function supports(FormInterface $form): bool
    {
        return $this->isCollection($form->getConfig()->getType());
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
