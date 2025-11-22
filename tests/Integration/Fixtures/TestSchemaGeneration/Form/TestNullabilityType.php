<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/** @template-extends AbstractType<mixed> */
final class TestNullabilityType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paramNotNullable1', TextType::class, ['constraints' => [new NotNull()]])
            ->add('paramNotNullable2', TextType::class, ['constraints' => [new NotBlank()]])
            ->add('paramNullable1', TextType::class, ['constraints' => [new NotBlank(['allowNull' => true])]])
            ->add('paramNullable2', TextType::class);
    }
}
