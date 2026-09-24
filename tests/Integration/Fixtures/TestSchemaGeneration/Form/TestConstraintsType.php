<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;

/** @template-extends AbstractType<mixed> */
final class TestConstraintsType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('greaterThan', IntegerType::class, ['constraints' => [new GreaterThan(7)]])
            ->add('lessThan', IntegerType::class, ['constraints' => [new LessThan(13)]])
            ->add('length', TextType::class, ['constraints' => [new Length(min: 4, max: 6)]])
            ->add('divisibleBy', NumberType::class, ['constraints' => [new Constraints\DivisibleBy(0.5)]])
            ->add('range', RangeType::class, ['constraints' => [new Constraints\Range(min: -20, max: -4)]])
            ->add('regex', TextType::class, ['constraints' => [new Constraints\Regex('/^[0-9]{1,5}$/i')]])
            ->add('greaterThanDate', DateType::class, ['widget' => 'single_text', 'constraints' => [new GreaterThan('today')]])
            ->add('lessThanPropertyPath', IntegerType::class, ['constraints' => [new LessThan(propertyPath: 'greaterThan')]])
            ->add('regexBracketDelimiters', TextType::class, ['constraints' => [new Constraints\Regex('{^[a-z]{2,}$}')]])
            ->add('regexUnicode', TextType::class, ['constraints' => [new Constraints\Regex('/^[a-z]+$/u')]])
            ->add('regexNotMatching', TextType::class, ['constraints' => [new Constraints\Regex(pattern: '/^admin/', match: false)]])
            ->add(
                'all',
                CollectionType::class,
                ['entry_type' => TextType::class, 'constraints' => [new Constraints\All([new Length(max: 5)])]],
            )
            ->add(
                'atLeastOneOf',
                TextType::class,
                ['constraints' => [new Constraints\AtLeastOneOf([new Length(exactly: 2), new Length(exactly: 10)])]],
            )
            ->add('email', TextType::class, ['constraints' => [new Constraints\Email()]]);
    }
}
