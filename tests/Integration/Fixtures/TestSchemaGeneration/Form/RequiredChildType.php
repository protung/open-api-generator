<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/** @template-extends AbstractType<mixed> */
final class RequiredChildType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paramRequired', TextType::class, ['required' => true])
            ->add('paramRequiredWithCustomDescription', TextType::class, ['required' => true, 'help' => 'My Description'])
            ->add('paramOptional', TextType::class, ['required' => false]);
    }
}
