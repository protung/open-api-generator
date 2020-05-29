<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Model;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use RuntimeException;
use Speicher210\OpenApiGenerator\Resolver\DefinitionName;
use function array_key_exists;
use function implode;
use function md5;
use function serialize;
use function sprintf;

final class ModelRegistry
{
    /** @var array<string,Model> */
    private array $models = [];

    /** @var array<string,Model> */
    private array $referencedModels = [];

    private DefinitionName $definitionNameResolver;

    public function __construct(DefinitionName $definitionNameResolver)
    {
        $this->definitionNameResolver = $definitionNameResolver;
    }

    public function schemaExistsForDefinition(Definition $definition) : bool
    {
        return array_key_exists(
            $this->definitionKey($definition),
            $this->models
        );
    }

    private function getModelWithDefinition(Definition $definition) : Model
    {
        if ($this->schemaExistsForDefinition($definition)) {
            return $this->models[$this->definitionKey($definition)];
        }

        throw new RuntimeException(
            sprintf(
                'Model with class name "%s" and serialization groups "%s" does not exist.',
                $definition->className(),
                implode(', ', $definition->serializationGroups())
            )
        );
    }

    public function getSchema(Definition $definition) : Schema
    {
        return $this->getModelWithDefinition($definition)->schema();
    }

    public function getReference(Definition $definition) : Reference
    {
        return new Reference(['$ref' => $this->definitionNameResolver->getReference($definition)]);
    }

    public function addSchema(Definition $definition, Schema $schema) : void
    {
        if ($this->schemaExistsForDefinition($definition)) {
            throw new RuntimeException(
                sprintf(
                    'Model with class name "%s" and serialization groups "%s" already exists.',
                    $definition->className(),
                    implode(', ', $definition->serializationGroups())
                )
            );
        }

        $this->models[$this->definitionKey($definition)] = new Model($definition, $schema);
    }

    public function createReference(Definition $definition) : void
    {
        $hash = $this->definitionKey($definition);

        $this->referencedModels[$hash] = $this->getModelWithDefinition($definition);
    }

    /**
     * @return Model[]
     */
    public function referencedModels() : array
    {
        return $this->referencedModels;
    }

    private function definitionKey(Definition $definition) : string
    {
        return md5(serialize([$definition->className(), $definition->serializationGroups()]));
    }
}
