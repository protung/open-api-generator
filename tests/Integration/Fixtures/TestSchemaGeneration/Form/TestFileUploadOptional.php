<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

/** @template-extends AbstractType<mixed> */
final class TestFileUploadOptional extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'paramFileOptional',
                FileType::class,
                [
                    'required' => false,
                    'constraints' => new File(maxSize: 1023),
                ],
            )
            ->add('paramExtra', TextType::class);
    }
}
