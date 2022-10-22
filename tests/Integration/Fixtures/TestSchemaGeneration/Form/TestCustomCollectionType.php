<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TestCustomCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'error_bubbling' => false,
                'entry_type' => TestInnerType::class,
                'allow_add' => true,
                'constraints' => [],
            ],
        );
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
