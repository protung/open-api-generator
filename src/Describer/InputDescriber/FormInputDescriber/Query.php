<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\InputDescriber\FormInputDescriber;

use cebe\openapi\spec\Parameter;
use Speicher210\OpenApiGenerator\Describer\Form\NameResolver;
use Speicher210\OpenApiGenerator\Describer\FormDescriber;
use Symfony\Component\Form\FormInterface;
use function array_filter;
use function array_merge;
use function implode;
use function nl2br;
use function sprintf;
use const PHP_EOL;

final class Query
{
    private const PARAMETER_LOCATION_QUERY = 'query';

    private FormDescriber $formDescriber;

    public function __construct(FormDescriber $formDescriber)
    {
        $this->formDescriber = $formDescriber;
    }

    /**
     * @return Parameter[]
     */
    public function describe(FormInterface $form) : array
    {
        return $this->processParametersFromForm($form, new NameResolver\FlatArray());
    }

    /**
     * @return Parameter[]
     */
    private function processParametersFromForm(FormInterface $form, NameResolver $nameResolver) : array
    {
        $parameters = [];
        if ($form->count() === 0) {
            $parameters[] = $this->createParameter($form, $nameResolver);

            return $parameters;
        }

        $childParameters = [];
        foreach ($form->all() as $child) {
            $childParameters[] = $this->processParametersFromForm($child, $nameResolver);
        }

        return array_merge($parameters, ...$childParameters);
    }

    private function createParameter(FormInterface $form, NameResolver $nameResolver) : Parameter
    {
        $formConfig = $form->getConfig();

        $name = $nameResolver->getPropertyName($form);

        $parameter   = new Parameter(['name' => $name, 'in' => self::PARAMETER_LOCATION_QUERY]);
        $description = $form->getConfig()->getOption('label');
        if ($description !== null) {
            $parameter->description = $description;
        }

        $parameter->schema = $this->formDescriber->createSchema($form, new NameResolver\FlatArray(), 'GET');

        $parameter->required = $formConfig->getRequired();

        if ($formConfig->getRequired() === true) {
            $parentForm = $form->getParent();
            if ($parentForm !== null && ! $parentForm->isRoot() && $parentForm->isRequired() === false) {
                $parameter->required    = false;
                $parameter->description = $this->updateDescription(
                    $parameter->description,
                    sprintf('Field required for %s', $nameResolver->getPropertyName($parentForm))
                );
            }
        }

        return $parameter;
    }

    private function updateDescription(?string $originalDescription, string $newText) : string
    {
        return nl2br(implode(PHP_EOL, array_filter([$originalDescription, $newText])), false);
    }
}
