<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form\PropertyDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\FormInterface;

use function count;
use function is_array;

final class SymfonyBuiltInPropertyDescriber implements PropertyDescriber
{
    public function describe(Schema $schema, string $blockPrefix, FormInterface $form): void
    {
        $formConfig = $form->getConfig();

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
                $schema->type   = Type::STRING;
                $schema->format = 'date';
                break;
            case 'datetime':
            case 'date_time':
                $schema->type   = Type::STRING;
                $schema->format = 'date-time';
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
                $choices      = $formConfig->getOption('choices');
                if ($choices !== null && is_array($choices) === true && count($choices) > 0) {
                    $schema->enum = (new ArrayChoiceList($choices))->getValues();
                } else {
                    $choiceLoader = $formConfig->getOption('choice_loader');
                    if ($choiceLoader instanceof ChoiceLoaderInterface) {
                        $schema->enum = $choiceLoader->loadChoiceList()->getValues();
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
                $parentType = $formConfig->getType()->getParent();
                $parentForm = $form->getParent();
                if ($parentType !== null && $parentForm !== null) {
                    $this->describe($schema, $parentType->getBlockPrefix(), $parentForm);
                }
        }
    }

    public function supports(string $blockPrefix): bool
    {
        // we support any type, as we will fallback on string.
        return true;
    }
}
