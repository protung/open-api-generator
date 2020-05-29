<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form\NameResolver;

use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use function array_pop;
use function array_reverse;
use function in_array;

trait FlatNameResolver
{
    /**
     * @return string[]
     */
    private function namesFromForm(FormInterface $form) : array
    {
        $names   = [];
        $names[] = $form->getName();

        while ($form->getParent() !== null) {
            $form    = $form->getParent();
            $names[] = $form->getName();
        }

        array_pop($names);

        $names = array_reverse($names);

        return $names;
    }

    /**
     * @param string[] $names
     */
    private function fromArray(string $name, array $names, FormConfigInterface $formConfig) : string
    {
        foreach ($names as $subName) {
            $name .= '[' . $subName . ']';
        }

        $blockPrefix = $formConfig->getType()->getBlockPrefix();

        if ($blockPrefix === 'collection') {
            $name .= '[]';
        }

        if (in_array($blockPrefix, ['choice', 'entity'], true) === true && (bool) $formConfig->getOption('multiple')) {
            $name .= '[]';
        }

        return $name;
    }
}
