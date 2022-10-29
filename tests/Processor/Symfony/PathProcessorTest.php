<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Model;

use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Describer\ExampleDescriber\ExampleDescriber;
use Protung\OpenApiGenerator\Describer\Form\FormFactory;
use Protung\OpenApiGenerator\Describer\InputDescriber;
use Protung\OpenApiGenerator\Describer\ObjectDescriber;
use Protung\OpenApiGenerator\Describer\OperationDescriber;
use Protung\OpenApiGenerator\Describer\OutputDescriber;
use Protung\OpenApiGenerator\Model\ModelRegistry;
use Protung\OpenApiGenerator\Processor\Path\Symfony\PathProcessor;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouteCollection;

final class PathProcessorTest extends TestCase
{
    public function testProcessRouteExceptionThrownIfMethodsNotDefined(): void
    {
        $operationDescriber = new OperationDescriber(
            new InputDescriber(
                $this->createMock(InputDescriber\InputDescriber::class)
            ),
            new OutputDescriber(
                new ObjectDescriber(new ModelRegistry()),
                new FormFactory($this->createMock(FormFactoryInterface::class)),
                $this->createMock(ExampleDescriber::class),
            )
        );
        $pathProcessor = new PathProcessor(
            $this->createMock(RouteCollection::class),
            $operationDescriber
        );

        self::markTestIncomplete('Implement');
    }
}
