<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TestFormDefinitionOptions extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('child', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('form_option');
        $resolver->setAllowedTypes('form_option', 'int');
    }
}
