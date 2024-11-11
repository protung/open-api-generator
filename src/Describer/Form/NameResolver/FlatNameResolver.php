<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form\NameResolver;

use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

use function array_pop;
use function array_reverse;

trait FlatNameResolver
{
    /**
     * @param FormInterface<mixed> $form
     *
     * @return string[]
     */
    private function namesFromForm(FormInterface $form): array
    {
        $names   = [];
        $names[] = $form->getName();

        while (($form = $form->getParent()) !== null) {
            $names[] = $form->getName();
        }

        array_pop($names);

        $names = array_reverse($names);

        return $names;
    }

    /**
     * @param FormConfigInterface<mixed> $formConfig
     * @param string[]                   $names
     */
    private function fromArray(string $name, array $names, FormConfigInterface $formConfig): string
    {
        foreach ($names as $subName) {
            $name .= '[' . $subName . ']';
        }

        $formType = $formConfig->getType();

        if ($this->hasBlockPrefix($formType, 'collection')) {
            $name .= '[]';
        }

        if (($this->hasBlockPrefix($formType, 'choice') || $this->hasBlockPrefix($formType, 'entity')) && (bool) $formConfig->getOption('multiple')) {
            $name .= '[]';
        }

        return $name;
    }

    private function hasBlockPrefix(ResolvedFormTypeInterface $formType, string $blockPrefix): bool
    {
        if ($formType->getBlockPrefix() === $blockPrefix) {
            return true;
        }

        $parentType = $formType->getParent();
        if ($parentType !== null) {
            return $this->hasBlockPrefix($parentType, $blockPrefix);
        }

        return false;
    }
}
