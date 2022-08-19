<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Model;

use cebe\openapi\spec\Schema;

use function array_key_last;
use function explode;

final class ReferenceModel
{
    private Model $model;

    private string $referencePath;

    public function __construct(Model $model, string $referencePath)
    {
        $this->model         = $model;
        $this->referencePath = $referencePath;
    }

    public function definition(): Definition
    {
        return $this->model->definition();
    }

    public function schema(): Schema
    {
        return $this->model->schema();
    }

    public function referenceName(): string
    {
        $paths = explode('/', $this->referencePath);

        return $paths[array_key_last($paths)];
    }

    public function referencePath(): string
    {
        return $this->referencePath;
    }
}
