<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Model;

use PHPUnit\Framework\TestCase;
use Speicher210\OpenApiGenerator\Model\Response;

final class ResponseTest extends TestCase
{
    public function testWithDescriptionUpdatesDescription(): void
    {
        $response = Response::for202()->withDescription('test description');

        self::assertSame('test description', $response->description());
    }
}
