<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

/** @template-extends AbstractType<mixed> */
final class TestDictionaryType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'error_bubbling' => false,
                'entry_type' => TestInnerType::class,
                'allow_add' => true,
                'constraints' => [
                    new Count(max: 5),
                ],
            ],
        );
    }

    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }
}
