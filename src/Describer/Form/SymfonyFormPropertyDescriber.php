<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form;

use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Describer\Form\PropertyDescriber\PropertyDescriber;
use Protung\OpenApiGenerator\Describer\FormDescriber;
use Psl;
use RuntimeException;
use Symfony\Component\Form\FormInterface;

use function is_array;

final class SymfonyFormPropertyDescriber
{
    /** @var array<PropertyDescriber> */
    private array $propertyDescribers;

    public function __construct(PropertyDescriber ...$propertyDescribers)
    {
        $this->propertyDescribers = $propertyDescribers;
    }

    /** @param FormInterface<mixed> $form */
    public function describe(Schema $schema, FormInterface $form, FormDescriber $formDescriber): void
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
            if ($propertyDescriber->supports($form)) {
                $propertyDescriber->describe($schema, $form, $formDescriber);
                $this->describeHelp($schema, $form);

                return;
            }
        }

        throw new RuntimeException('No property describer supports "' . $form->getConfig()->getType()->getBlockPrefix() . '".');
    }

    /** @param FormInterface<mixed> $form */
    private function describeHelp(Schema $schema, FormInterface $form): void
    {
        $attr = $form->getConfig()->getOption('attr');
        if (is_array($attr) && isset($attr['placeholder'])) {
            $schema->example = $attr['placeholder'];
        }

        $description = $form->getConfig()->getOption('help');
        if ($description === null) {
            return;
        }

        $schema->description = Psl\Type\string()->coerce($description);
    }
}
