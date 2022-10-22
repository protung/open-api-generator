<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

final class TestFileUpload extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'paramFile',
                FileType::class,
                [
                    'constraints' => [
                        new Image(
                            [
                                'maxSize' => 1234,
                                'minWidth' => 100,
                                'maxWidth' => 200,
                                'minHeight' => 300,
                                'maxHeight' => 400,
                            ],
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
                            [
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/png',
                                    'image/jpeg',
                                ],
                                'maxSize' => '5Mi',
                            ],
                        ),
                    ],
                ],
            )
            ->add('paramExtra', TextType::class);
    }
}
