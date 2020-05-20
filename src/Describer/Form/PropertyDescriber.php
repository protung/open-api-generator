<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\Form;

use App\Doctrine\Type\UuidType;
use App\Form\Type\Api\BooleanType;
use App\Model\Uuid;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Doctrine\DBAL\Types\Type as DoctrineType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;

final class PropertyDescriber implements PropertyDescriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @todo custom specific logic in different service can go here (like identity, sylius resource, polymorphic, etc).
     */
    public function describe(Schema $schema, string $blockPrefix, FormInterface $form): void
    {
        if ($blockPrefix === 'form') {
            return;
        }

        $formConfig = $form->getConfig();

        switch ($blockPrefix) {
            case 'integer':
                $schema->type = Type::INTEGER;
                break;
            case 'number':
                $schema->type = Type::NUMBER;
                break;
            case 'date':
                $schema->type = Type::STRING;
                $schema->format = 'date';
                break;
            case 'datetime':
            case 'date_time':
                $schema->type = Type::STRING;
                $schema->format = 'date-time';
                break;
            case 'text':
            case 'string':
                $schema->type = Type::STRING;
                break;
            case 'uuid':  // @todo custom
            case 'identity':  // @todo custom
                $schema->type = Type::STRING;
                $schema->format = 'uuid';
                $schema->pattern = Uuid::VALID_STRING_PATTERN;
                break;
            case 'email':
                $schema->type = Type::STRING;
                $schema->format = 'email';
                break;
            case 'boolean':  // @todo custom
                $schema->type = Type::STRING; // @todo check type of symfony boolean type.
                $schema->enum = [BooleanType::VALUE_FALSE, BooleanType::VALUE_TRUE];
                break;
            case 'choice':
                $schema->type = Type::STRING;
                $choices = $formConfig->getOption('choices');
                if ($choices !== null && \is_array($choices) === true && \count($choices) > 0) {
                    $schema->enum = (new ArrayChoiceList($choices))->getValues();
                } else {
                    $choiceLoader = $formConfig->getOption('choice_loader');
                    if ($choiceLoader instanceof ChoiceLoaderInterface) {
                        $schema->enum = $choiceLoader->loadChoiceList()->getValues();
                    }
                }
                break;
            case 'file':
                $schema->type = Type::STRING;
                $schema->format = 'binary';
                break;
            case 'entity':
            case 'resource': // @todo custom
                $this->describeEntity($schema, $formConfig);
                break;
            case 'password':
                $schema->type = Type::STRING;
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

    private function describeEntity(Schema $schema, FormConfigInterface $formConfig): void
    {
        $entityClass = $formConfig->getOption('class');
        $metadata = $this->entityManager->getClassMetadata($entityClass);

        if ((bool) $formConfig->getOption('multiple') === true) {
            $schema->type = Type::ARRAY;

            $schemaToConfigure = $schema->items;
        } else {
            $schemaToConfigure = $schema;
        }

        $type = $metadata->getTypeOfField($metadata->getSingleIdentifierFieldName());

        if ($type === null) {
            // @todo try to determine type somehow.
            $schemaToConfigure->type = Type::STRING;
        } elseif (DoctrineType::getType($type) instanceof UuidType) {
            $schemaToConfigure->type = Type::STRING;
            $schemaToConfigure->format = 'uuid';
            $schemaToConfigure->pattern = Uuid::VALID_STRING_PATTERN;
        } else {
            $schemaToConfigure->type = $type;
        }
    }
}
