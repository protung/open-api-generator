<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests;

use Metadata\MetadataFactoryInterface;
use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\GeneratorFactory;
use Psl\Exception\InvariantViolationException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class GeneratorFactoryTest extends TestCase
{
    public function testTheJmsServicesArePassedTogether(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('Pass both the JMS Serializer metadata factory and the JMS Serializer, or neither.');

        GeneratorFactory::create(
            '1.0.0',
            self::createStub(RouterInterface::class),
            self::createStub(FormFactoryInterface::class),
            self::createStub(ValidatorInterface::class),
            self::createStub(MetadataFactoryInterface::class),
        );
    }
}
