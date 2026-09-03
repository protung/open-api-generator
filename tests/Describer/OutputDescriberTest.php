<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Describer\ExampleDescriber\CompoundExampleDescriber;
use Protung\OpenApiGenerator\Describer\Form\FormFactory;
use Protung\OpenApiGenerator\Describer\ObjectDescriber;
use Protung\OpenApiGenerator\Describer\OutputDescriber;
use Protung\OpenApiGenerator\Model\ModelRegistry;
use Protung\OpenApiGenerator\Model\Path\Output\SymfonyValidatedPayloadErrorOutput;
use Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures\PairRequest;
use Symfony\Component\Form\FormFactoryBuilder;

final class OutputDescriberTest extends TestCase
{
    public function testAValidatedPayloadErrorCanNotBeDescribedWithoutAValidator(): void
    {
        $outputDescriber = new OutputDescriber(
            new ObjectDescriber(new ModelRegistry()),
            new FormFactory((new FormFactoryBuilder())->getFormFactory()),
            new CompoundExampleDescriber(),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Can not handle object to describe of type'
            . ' "Protung\OpenApiGenerator\Model\Path\Output\SymfonyValidatedPayloadErrorOutput"',
        );

        $outputDescriber->describe(SymfonyValidatedPayloadErrorOutput::forClass(PairRequest::class));
    }
}
