<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Describer\ObjectDescriber;
use Protung\OpenApiGenerator\Describer\OutputDescriber;
use Protung\OpenApiGenerator\Model\ModelRegistry;
use Protung\OpenApiGenerator\Model\Path\Output\SymfonyPayloadValidationProblemOutput;
use Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures\PairRequest;

final class OutputDescriberTest extends TestCase
{
    public function testAnOutputNoRegisteredDescriberSupportsIsRejected(): void
    {
        $outputDescriber = new OutputDescriber(new ObjectDescriber(new ModelRegistry()));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Can not handle object to describe of type'
            . ' "Protung\OpenApiGenerator\Model\Path\Output\SymfonyPayloadValidationProblemOutput"',
        );

        $outputDescriber->describe(SymfonyPayloadValidationProblemOutput::forClass(PairRequest::class));
    }
}
