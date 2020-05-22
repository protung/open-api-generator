<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use RuntimeException;
use Speicher210\OpenApiGenerator\Resolver\DefinitionName;
use function sprintf;

final class ModelRegistry
{
    /** @var Model[] */
    private array $models = [];

    private DefinitionName $definitionNameResolver;

    public function __construct(DefinitionName $definitionNameResolver)
    {
        $this->definitionNameResolver = $definitionNameResolver;
    }

    public function schemaExistsForDefinition(Definition $definition) : bool
    {
        foreach ($this->models as $model) {
            $modelDefinition = $model->definition();
            if ($definition->hash() === $modelDefinition->hash()) {
                return true;
            }
        }

        return false;
    }

    private function getModelWithDefinition(Definition $definition) : Model
    {
        foreach ($this->models as $model) {
            $modelDefinition = $model->definition();
            if ($definition->hash() === $modelDefinition->hash()) {
                return $model;
            }
        }

        throw new RuntimeException(
            sprintf('Model with definition name "%s" does not exist.', $definition->hash())
        );
    }

    public function getSchema(Definition $definition) : Schema
    {
        return $this->getModelWithDefinition($definition)->schema();
    }

    public function getReference(Definition $definition) : Reference
    {
        // todo refactor this. this method is called just so an exception is thrown if there is no model yet for the definition
        $this->getModelWithDefinition($definition);

        return new Reference(['$ref' => $this->definitionNameResolver->getReference($definition)]);
    }

    public function addSchema(Definition $definition, Schema $schema) : void
    {
        if ($this->schemaExistsForDefinition($definition)) {
            throw new RuntimeException(
                sprintf('Model with definition name "%s" already exists.', $definition->hash())
            );
        }

        $this->models[] = new Model($definition, $schema);
    }

    /**
     * @return Model[]
     */
    public function models() : array
    {
        return $this->models;
    }
}
