<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class QueryType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paramNoType')
            ->add(
                'paramWithCustomDescriptionAndExample',
                TextType::class,
                ['help' => 'customLabel', 'attr' => ['placeholder' => 'my-query-example']]
            )
            ->add('innerForm', TestType::class)
            ->add('paramRequiredChildren', RequiredChildType::class, ['required' => false]);
    }
}
