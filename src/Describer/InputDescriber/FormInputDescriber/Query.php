<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\InputDescriber\FormInputDescriber;

use cebe\openapi\spec\Parameter;
use Protung\OpenApiGenerator\Describer\Form\NameResolver;
use Protung\OpenApiGenerator\Describer\FormDescriber;
use Protung\OpenApiGenerator\Describer\SpecificationDescriber;
use Psl;
use Symfony\Component\Form\FormInterface;

final class Query
{
    private const PARAMETER_LOCATION_QUERY = 'query';

    private FormDescriber $formDescriber;

    public function __construct(FormDescriber $formDescriber)
    {
        $this->formDescriber = $formDescriber;
    }

    /**
     * @param FormInterface<mixed> $form
     *
     * @return list<Parameter>
     */
    public function describe(FormInterface $form): array
    {
        if ($form->isRoot() && $form->count() === 0) {
            $nameResolver = new NameResolver\PrefixedFlatArray('additionalProp');
        } else {
            $nameResolver = new NameResolver\FlatArray();
        }

        return $this->processParametersFromForm($form, $nameResolver);
    }

    /**
     * @param FormInterface<mixed> $form
     *
     * @return list<Parameter>
     */
    private function processParametersFromForm(FormInterface $form, NameResolver $nameResolver): array
    {
        if ($form->count() === 0) {
            return [$this->createParameter($form, $nameResolver)];
        }

        return Psl\Vec\flat_map(
            $form->all(),
            fn (FormInterface $child) => $this->processParametersFromForm($child, $nameResolver),
        );
    }

    /** @param FormInterface<mixed> $form */
    private function createParameter(FormInterface $form, NameResolver $nameResolver): Parameter
    {
        $formConfig = $form->getConfig();

        $name = $nameResolver->getPropertyName($form);

        $parameter   = new Parameter(['name' => $name, 'in' => self::PARAMETER_LOCATION_QUERY]);
        $description = $form->getConfig()->getOption('help');
        if ($description !== null) {
            $parameter->description = Psl\Type\string()->coerce($description);
        }

        $parameter->schema = $this->formDescriber->addDeepSchema($form, new NameResolver\FlatArray());

        $parameter->required = $formConfig->getRequired();

        if ($formConfig->getRequired() === true) {
            $parentForm = $form->getParent();
            if ($parentForm !== null && ! $parentForm->isRoot() && $parentForm->isRequired() === false) {
                $parameter->required    = false;
                $parameter->description = SpecificationDescriber::updateDescription(
                    $parameter->description,
                    Psl\Str\format('Field required for %s', $nameResolver->getPropertyName($parentForm)),
                );
            }
        }

        return $parameter;
    }
}
