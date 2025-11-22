<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Override;
use Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\NotDescribedObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @template-extends AbstractType<mixed> */
final class TestDataClassType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('stringProperty', TextType::class);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => NotDescribedObject::class,
            ],
        );
    }
}
