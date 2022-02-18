<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\InputDescriber\FormInputDescriber;

use cebe\openapi\spec\Parameter;
use Psl;
use Speicher210\OpenApiGenerator\Describer\Form\NameResolver;
use Speicher210\OpenApiGenerator\Describer\FormDescriber;
use Speicher210\OpenApiGenerator\Describer\SpecificationDescriber;
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
     * @return list<Parameter>
     */
    public function describe(FormInterface $form): array
    {
        return $this->processParametersFromForm($form, new NameResolver\FlatArray());
    }

    /**
     * @return list<Parameter>
     */
    private function processParametersFromForm(FormInterface $form, NameResolver $nameResolver): array
    {
        if ($form->count() === 0) {
            return [$this->createParameter($form, $nameResolver)];
        }

        return Psl\Vec\flat_map(
            $form->all(),
            fn (FormInterface $child) => $this->processParametersFromForm($child, $nameResolver)
        );
    }

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
                    Psl\Str\format('Field required for %s', $nameResolver->getPropertyName($parentForm))
                );
            }
        }

        return $parameter;
    }
}
