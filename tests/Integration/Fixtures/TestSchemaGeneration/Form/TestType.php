<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\Enum\IntegerBackedEnum;
use Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Model\Enum\StringBackedEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\DivisibleBy;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Unique;

use function range;

final class TestType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paramNoType')
            ->add('paramBoolean', TestBooleanType::class)
            ->add('paramCheckbox', CheckboxType::class)
            ->add('paramInt', IntegerType::class, ['constraints' => [new GreaterThan(7), new LessThan(13)]])
            ->add('paramText', TextType::class, ['constraints' => [new Length(['min' => 4, 'max' => 6])]])
            ->add('paramHidden', HiddenType::class)
            ->add(
                'paramNumber',
                NumberType::class,
                [
                    'constraints' => [new All([new GreaterThanOrEqual(3.14), new LessThanOrEqual(9.9)])],
                ]
            )
            ->add(
                'paramNumberDivisibleBy',
                NumberType::class,
                [
                    'constraints' => [new DivisibleBy(0.5)],
                ]
            )
            ->add('paramInRange', RangeType::class, ['constraints' => [new Range(['min' => -20, 'max' => -4])]])
            ->add(
                'paramDateSingleText',
                DateType::class,
                ['widget' => 'single_text']
            )
            ->add(
                'paramDateChoice',
                DateType::class,
                ['widget' => 'choice']
            )
            ->add('paramDateTimeSingleText', DateTimeType::class, ['widget' => 'single_text', 'years' => range(2015, 2025)])
            ->add('paramDateTimeChoice', DateTimeType::class, ['years' => range(2015, 2025)])
            ->add('paramTimeSingleText', TimeType::class, ['widget' => 'single_text'])
            ->add('paramTimeChoice', TimeType::class, ['widget' => 'choice', 'hours' => range(0, 15), 'minutes' => [10, 20, 50]])
            ->add('paramEmail', EmailType::class, ['constraints' => [new Unique()]])
            ->add('paramChoice', ChoiceType::class, ['choices' => ['a', 'b']])
            ->add('paramChoiceWithLoader', ChoiceType::class, ['choice_loader' => new CallbackChoiceLoader(static fn (): array => [1, 2, 3])])
            ->add(
                'paramChoiceWithChoiceValueCallable',
                ChoiceType::class,
                [
                    'choices' => ['a', 'b'],
                    'choice_value' => static fn (?string $choice): string => 'choice_value_with_callable: ' . $choice,
                ]
            )
            ->add(
                'paramChoiceWithLoaderAndChoiceValueCallable',
                ChoiceType::class,
                [
                    'choice_loader' => new CallbackChoiceLoader(static fn (): array => [1, 2, 3]),
                    'choice_value' => static fn (?string $choice): string => 'choice_value_with_loader_and_callable: ' . $choice,
                ]
            )
            ->add('paramPassword', PasswordType::class)
            ->add(
                'paramCollection',
                CollectionType::class,
                [
                    'entry_type' => TextType::class,
                    'constraints' => [new Count(['min' => 3, 'max' => 44])],
                ]
            )
            ->add('paramCustomCollection', TestCustomCollectionType::class)
            ->add('paramDictionaryType', TestDictionaryType::class)
            ->add(
                'paramCustom',
                TestInnerType::class,
                [
                    'constraints' => [
                        new Count(['min' => 3, 'max' => 44]),
                    ],
                ]
            )
            ->add('paramExtended', TestExtendedType::class)
            ->add('paramExtendedExtended', TestExtendedExtendedType::class)
            ->add(
                'paramWithExampleAndDescription',
                TextType::class,
                ['help' => 'Custom description.', 'attr' => ['placeholder' => 'my-example']]
            )
            ->add('paramWithDataClassRequired', TestDataClassType::class, ['constraints' => [new NotBlank()]])
            ->add('paramWithDataClassOptional', TestDataClassType::class)
            ->add('paramStringBackedEnum', EnumType::class, ['class' => StringBackedEnum::class])
            ->add('paramIntegerBackedEnum', EnumType::class, ['class' => IntegerBackedEnum::class]);
    }
}
