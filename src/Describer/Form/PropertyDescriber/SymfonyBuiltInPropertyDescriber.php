<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form\PropertyDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Describer\FormDescriber;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

use function array_map;
use function array_values;
use function count;
use function is_array;
use function is_callable;

final class SymfonyBuiltInPropertyDescriber implements PropertyDescriber
{
    public function describe(Schema $schema, FormInterface $form, FormDescriber $formDescriber): void
    {
        $formConfig = $form->getConfig();

        if ($formConfig->getCompound() === true) {
            $schema->type = Type::OBJECT;
        }

        $this->describeProperty($schema, $formConfig->getType(), $formConfig);
    }

    private function describeProperty(
        Schema $schema,
        ResolvedFormTypeInterface $formType,
        FormConfigInterface $formConfig
    ): void {
        $blockPrefix = $formType->getBlockPrefix();

        switch ($blockPrefix) {
            case 'boolean':
            case 'checkbox':
                $schema->type = Type::BOOLEAN;
                break;
            case 'integer':
                $schema->type = Type::INTEGER;
                break;
            case 'number':
                $schema->type = Type::NUMBER;
                break;
            case 'date':
                if ($formConfig->getOption('widget') === 'single_text') {
                    $schema->type   = Type::STRING;
                    $schema->format = 'date';
                }

                break;
            case 'datetime':
            case 'date_time':
                if ($formConfig->getOption('widget') === 'single_text') {
                    $schema->type   = Type::STRING;
                    $schema->format = 'date-time';
                }

                break;
            case 'time':
                if ($formConfig->getOption('widget') === 'single_text') {
                    $schema->type = Type::STRING;
                }

                break;
            case 'text':
            case 'hidden':
            case 'string':
                $schema->type = Type::STRING;
                break;
            case 'email':
                $schema->type   = Type::STRING;
                $schema->format = 'email';
                break;
            case 'choice':
                $schema->type = Type::STRING;

                $choices = $formConfig->getOption('choices');
                if ($choices !== null && is_array($choices) === true && count($choices) > 0) {
                    $choiceValue = $formConfig->getOption('choice_value');
                    if (is_callable($choiceValue)) {
                        $schema->enum = array_map(
                            $choiceValue,
                            (new ArrayChoiceList($choices))->getValues()
                        );
                    } else {
                        $schema->enum = (new ArrayChoiceList($choices))->getValues();
                    }
                } else {
                    $choiceLoader = $formConfig->getOption('choice_loader');
                    if ($choiceLoader instanceof ChoiceLoaderInterface) {
                        $choiceValue = $formConfig->getOption('choice_value');
                        if (is_callable($choiceValue)) {
                            $schema->enum = array_values(
                                array_map(
                                    $choiceValue,
                                    $choiceLoader->loadChoiceList()->getChoices()
                                )
                            );
                        } else {
                            $schema->enum = $choiceLoader->loadChoiceList()->getValues();
                        }
                    }
                }

                break;
            case 'file':
                $schema->type   = Type::STRING;
                $schema->format = 'binary';
                break;
            case 'password':
                $schema->type   = Type::STRING;
                $schema->format = 'password';
                break;
            default:
                $parentType = $formType->getParent();
                if ($parentType !== null) {
                    $this->describeProperty($schema, $parentType, $formConfig);
                }
        }
    }

    public function supports(FormInterface $form): bool
    {
        // we support any type, as we will fallback on string.
        return true;
    }
}
