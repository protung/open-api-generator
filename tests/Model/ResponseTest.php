<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Model;

use PHPUnit\Framework\TestCase;
use Speicher210\OpenApiGenerator\Assert\Exception\InvalidArgument;
use Speicher210\OpenApiGenerator\Model\Path\Output\FileOutput;
use Speicher210\OpenApiGenerator\Model\Response;

final class ResponseTest extends TestCase
{
    public function testWithDescriptionUpdatesDescription(): void
    {
        $response = Response::for202()->withDescription('test description');

        self::assertSame('test description', $response->description());
    }

    public function testExceptionIsThrownWhileInstantiatingWithMultipleOutputsForTheSameContentType(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Outputs must have different content types');

        new Response(200, [], FileOutput::forPdf(), FileOutput::forPdf());
    }
}
