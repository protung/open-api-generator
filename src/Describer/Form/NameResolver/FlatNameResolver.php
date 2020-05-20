<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form\NameResolver;

use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;

trait FlatNameResolver
{
    private function namesFromForm(FormInterface $form): array
    {
        $names = [];
        $names[] = $form->getName();

        while ($form->getParent()) {
            $form = $form->getParent();
            $names[] = $form->getName();
        }

        \array_pop($names);

        $names = \array_reverse($names);

        return $names;
    }

    private function fromArray(string $name, array $names, FormConfigInterface $formConfig): string
    {
        foreach ($names as $subName) {
            $name .= '[' . $subName . ']';
        }

        $blockPrefix = $formConfig->getType()->getBlockPrefix();

        if ($blockPrefix === 'collection') {
            $name .= '[]';
        }

        if (\in_array($blockPrefix, ['choice', 'entity'], true) === true && (bool) $formConfig->getOption('multiple')) {
            $name .= '[]';
        }

        return $name;
    }
}
