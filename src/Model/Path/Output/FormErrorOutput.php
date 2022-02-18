<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model\Path\Output;

use Speicher210\OpenApiGenerator\Model\FormDefinition;
use Speicher210\OpenApiGenerator\Model\Path\Output;

/**
 * @psalm-immutable
 */
final class FormErrorOutput implements Output
{
    private FormDefinition $formDefinition;

    public function __construct(FormDefinition $formDefinition)
    {
        $this->formDefinition = $formDefinition;
    }

    public function formDefinition(): FormDefinition
    {
        return $this->formDefinition;
    }

    public function example(): mixed
    {
        // @todo implement
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function contentTypes(): array
    {
        return [Output::CONTENT_TYPE_APPLICATION_JSON];
    }
}
