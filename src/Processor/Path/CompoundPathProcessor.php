<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Processor\Path;

use RuntimeException;
use Speicher210\OpenApiGenerator\Model\Path\Path;

use function get_class;

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
    public function process(Path $path): array
    {
        foreach ($this->pathProcessors as $pathProcessor) {
            if ($pathProcessor->canProcess($path)) {
                return $pathProcessor->process($path);
            }
        }

        throw new RuntimeException('Can not process path of type ' . get_class($path));
    }

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
