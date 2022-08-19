<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Model;

use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Model\Response;

final class ResponseTest extends TestCase
{
    public function testWithDescriptionUpdatesDescription(): void
    {
        $response = Response::for202()->withDescription('test description');

        self::assertSame('test description', $response->description());
    }
}
