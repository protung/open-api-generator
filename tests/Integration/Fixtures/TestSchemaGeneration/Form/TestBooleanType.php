<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Integration\Fixtures\TestSchemaGeneration\Form;

use Symfony\Component\Form\AbstractType;

/** @template-extends AbstractType<mixed> */
final class TestBooleanType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'boolean';
    }
}
