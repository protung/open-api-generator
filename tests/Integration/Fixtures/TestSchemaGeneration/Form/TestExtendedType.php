<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

/** @template-extends AbstractType<mixed> */
final class TestExtendedType extends AbstractType
{
    #[Override]
    public function getParent(): string
    {
        return EmailType::class;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'custom';
    }
}
