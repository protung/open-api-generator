<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer;

use Psl\Str;

use function nl2br;

use const PHP_EOL;

final class SpecificationDescriber
{
    /**
     * @psalm-pure
     */
    public static function updateDescription(?string $existingText, string $newText): string
    {
        if ($existingText === null) {
            return $newText;
        }

        $existingLines = Str\split($existingText, '<br>' . PHP_EOL);

        return nl2br(Str\join([...$existingLines, $newText], PHP_EOL), false);
    }
}
