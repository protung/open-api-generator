<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

final class TestExtendedType extends AbstractType
{
    public function getParent(): string
    {
        return EmailType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'custom';
    }
}
