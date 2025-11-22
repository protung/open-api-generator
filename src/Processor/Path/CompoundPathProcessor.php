<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Processor\Path;

use Override;
use Protung\OpenApiGenerator\Model\Path\Path;
use RuntimeException;

final class CompoundPathProcessor implements PathProcessor
{
    /** @var PathProcessor[] */
    private array $pathProcessors;

    public function __construct(PathProcessor ...$pathProcessors)
    {
        $this->pathProcessors = $pathProcessors;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function process(Path $path): array
    {
        foreach ($this->pathProcessors as $pathProcessor) {
            if ($pathProcessor->canProcess($path)) {
                return $pathProcessor->process($path);
            }
        }

        throw new RuntimeException('Can not process path of type ' . $path::class);
    }

    #[Override]
    public function canProcess(Path $path): bool
    {
        foreach ($this->pathProcessors as $pathProcessor) {
            if ($pathProcessor->canProcess($path)) {
                return true;
            }
        }

        return false;
    }
}
