<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form\PropertyDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Describer\Form\FormFactory;
use Protung\OpenApiGenerator\Describer\Form\NameResolver\FormName;
use Protung\OpenApiGenerator\Describer\FormDescriber;
use Protung\OpenApiGenerator\Model\FormDefinition;
use Psl;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

use function array_values;

final class DictionaryPropertyDescriber implements PropertyDescriber
{
    private FormFactory $formFactory;

    /** @var list<class-string<FormTypeInterface<mixed>>> */
    private array $supportedFormTypes;

    /**
     * @param class-string<FormTypeInterface<mixed>> ...$supportedFormTypes
     */
    public function __construct(FormFactory $formFactory, string ...$supportedFormTypes)
    {
        $this->formFactory        = $formFactory;
        $this->supportedFormTypes = array_values($supportedFormTypes);
    }

    /** @param FormInterface<mixed> $form */
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
                    Psl\Type\dict(Psl\Type\string(), Psl\Type\mixed())->coerce($formConfig->getOption('entry_options')),
                ),
            ),
            $form->getRoot()->getConfig()->getMethod(),
        );

        $schema->type                 = Type::OBJECT;
        $schema->additionalProperties = $formDescriber->addDeepSchema($subForm, new FormName());
    }

    /** @param FormInterface<mixed> $form */
    public function supports(FormInterface $form): bool
    {
        $resolvedFormType = $form->getConfig()->getType();

        if (! $this->isCollection($resolvedFormType)) {
            return false;
        }

        return Psl\Iter\any(
            $this->supportedFormTypes,
            static fn (string $supportedFormType): bool => $resolvedFormType->getInnerType() instanceof $supportedFormType,
        );
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
