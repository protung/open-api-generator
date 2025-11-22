<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Override;
use Symfony\Component\Form\AbstractType;

/** @template-extends AbstractType<mixed> */
final class TestExtendedExtendedType extends AbstractType
{
    #[Override]
    public function getParent(): string
    {
        return TestExtendedType::class;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'super_custom';
    }
}
