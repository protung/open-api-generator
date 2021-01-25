<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Tests\Describer;

use PHPUnit\Framework\TestCase;
use Speicher210\OpenApiGenerator\Describer\SpecificationDescriber;

final class SpecificationDescriberTest extends TestCase
{
    public function testUpdateDescription(): void
    {
        $description = null;

        $description = SpecificationDescriber::updateDescription($description, 'First line');
        self::assertSame('First line', $description);

        $description = SpecificationDescriber::updateDescription($description, 'Second line');
        self::assertSame("First line<br>\nSecond line", $description);

        $description = SpecificationDescriber::updateDescription($description, 'Third line');
        self::assertSame("First line<br>\nSecond line<br>\nThird line", $description);
    }
}
