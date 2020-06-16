<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class TestFileUpload extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paramFile', FileType::class)
            ->add('paramFileOptional', FileType::class, ['required' => false])
            ->add('paramExtra', TextType::class);
    }
}
