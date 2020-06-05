<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Unique;

final class TestType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('paramNoType')
            ->add('paramInt', IntegerType::class, ['constraints' => [new GreaterThan(7), new LessThan(13)]])
            ->add('paramText', TextType::class, ['constraints' => [new Length(['min' => 4, 'max' => 6])]])
            ->add(
                'paramNumber',
                NumberType::class,
                ['constraints' => [new GreaterThanOrEqual(3.14), new LessThanOrEqual(9.9)]]
            )
            ->add('paramInRange', RangeType::class, ['constraints' => [new Range(['min' => -20, 'max' => -4])]])
            ->add(
                'paramDate',
                DateType::class,
                ['widget' => 'single_text']
            )
            ->add('paramDateTime', DateTimeType::class)
            ->add('paramEmail', EmailType::class, ['constraints' => [new Unique()]])
            ->add('paramChoice', ChoiceType::class, ['choices' => ['a', 'b']])
            ->add('paramChoiceWithLoader', ChoiceType::class, [
                'choice_loader' => new CallbackChoiceLoader(static function () : array {
                    return [1, 2, 3];
                }),
            ])
            ->add('paramPassword', PasswordType::class)
            ->add(
                'paramCollection',
                CollectionType::class,
                [
                    'entry_type' => TextType::class,
                    'constraints' => [new Count(['min' => 3, 'max' => 44])],
                ]
            )
            ->add('paramCustom', TestInnerType::class)
            ->add('paramExtended', TestExtendedType::class)
            ->add(
                'paramWithExampleAndDescription',
                TextType::class,
                ['help' => 'Custom description.', 'attr' => ['placeholder' => 'my-example']]
            );
    }
}
