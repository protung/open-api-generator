<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

/** @template-extends AbstractType<mixed> */
final class TestFileUpload extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'paramFile',
                FileType::class,
                [
                    'constraints' => [
                        new Image(
                            maxSize: 1234,
                            minWidth: 100,
                            maxWidth: 200,
                            maxHeight: 400,
                            minHeight: 300,
                        ),
                    ],
                ],
            )
            ->add(
                'paramFileOptional',
                FileType::class,
                [
                    'required' => false,
                    'constraints' => [
                        new File(
                            maxSize: '5Mi',
                            mimeTypes: [
                                'application/pdf',
                                'image/png',
                                'image/jpeg',
                            ],
                        ),
                    ],
                ],
            )
            ->add('paramExtra', TextType::class);
    }
}
