<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

final class ModelRegistry
{
    /** @var Model[] */
    private array $models = [];

    public function hasModelWithDefinition(Definition $definition): bool
    {
        foreach ($this->models as $model) {
            $modelDefinition = $model->definition();
            if ($definition->hash() === $modelDefinition->hash()) {
                return true;
            }
        }

        return false;
    }

    public function addModel(Model $model): void
    {
        if ($this->hasModelWithDefinition($model->definition())) {
            throw new \RuntimeException(
                \sprintf('Model with definition name "%s" already exists.', $model->definition()->hash())
            );
        }
        $this->models[] = $model;
    }

    /**
     * @return Model[]
     */
    public function models(): array
    {
        return $this->models;
    }
}
