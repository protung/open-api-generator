<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\InputDescriber\FormInputDescriber;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Schema;
use Protung\OpenApiGenerator\Describer\Form\NameResolver;
use Protung\OpenApiGenerator\Describer\FormDescriber;
use Psl;
use Symfony\Component\Form\FormInterface;

use function array_intersect;
use function array_merge;

final class Body
{
    private const CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    private const CONTENT_TYPE_APPLICATION_FORM = 'application/x-www-form-urlencoded';

    private const CONTENT_TYPE_MULTIPART_FORM_DATA = 'multipart/form-data';

    private FormDescriber $formDescriber;

    public function __construct(FormDescriber $formDescriber)
    {
        $this->formDescriber = $formDescriber;
    }

    /**
     * The content media types are determined based on schema properties:
     * - if at least one required file property then only "multipart/form-data"
     * - else if no files at all then "application/json" and "application/x-www-form-urlencoded"
     * - otherwise "application/json", "application/x-www-form-urlencoded" for not file properties and "multipart/form-data" for all properties
     *
     * @param FormInterface<mixed> $form
     *
     * @return array<string, MediaType>
     */
    public function describe(FormInterface $form): array
    {
        $jsonSchema = $this->formDescriber->addDeepSchema($form, new NameResolver\FormName());

        $httpMethod = $form->getRoot()->getConfig()->getMethod();

        if ($form->isRoot() && $form->count() === 0) {
            $nameResolver = new NameResolver\PrefixedFlatArray('additionalProp');
        } else {
            $nameResolver = new NameResolver\FlatArray();
        }

        $formDataSchema = $this->formDescriber->addFlattenSchema($form, $nameResolver);

        $content = [];

        if ($this->schemaHasFileProperties($jsonSchema)) {
            if ($httpMethod === 'PATCH' || ! $this->schemaContainsRequiredFileProperties($jsonSchema)) {
                $content[self::CONTENT_TYPE_APPLICATION_JSON] = new MediaType(
                    ['schema' => $this->schemaWithoutFileProperties($jsonSchema)],
                );
                $content[self::CONTENT_TYPE_APPLICATION_FORM] = new MediaType(
                    ['schema' => $this->schemaWithoutFileProperties($formDataSchema)],
                );
            }

            $content[self::CONTENT_TYPE_MULTIPART_FORM_DATA] = new MediaType(['schema' => $formDataSchema]);
        } else {
            $content[self::CONTENT_TYPE_APPLICATION_JSON] = new MediaType(['schema' => $jsonSchema]);
            $content[self::CONTENT_TYPE_APPLICATION_FORM] = new MediaType(['schema' => $formDataSchema]);
        }

        return $content;
    }

    private function schemaWithoutFileProperties(Schema $schema): Schema
    {
        $schemaWithoutProperties = clone $schema;
        $this->removeFilePropertiesFromSchema($schemaWithoutProperties);

        return $schemaWithoutProperties;
    }

    private function removeFilePropertiesFromSchema(Schema $schema): void
    {
        if ($schema->properties === null || $schema->properties === []) {
            return;
        }

        $schema->properties = Psl\Dict\filter(
            Psl\Type\dict(Psl\Type\string(), Psl\Type\instance_of(Schema::class))->coerce($schema->properties),
            static function (Schema $property): bool {
                if ($property->format === 'binary') {
                    return false;
                }

                return $property->type !== 'array' || $property->items === null || ($property->items instanceof Schema && $property->items->format !== 'binary');
            },
        );

        foreach ($schema->properties as $property) {
            if ($property->type !== 'array') {
                continue;
            }

            $this->removeFilePropertiesFromSchema($property);
        }
    }

    private function schemaContainsRequiredFileProperties(Schema $schema): bool
    {
        if ($schema->required === null || $schema->required === []) {
            return false;
        }

        $fileProperties = $this->getFilePropertiesFromSchema($schema);
        if ($fileProperties === []) {
            return false;
        }

        return array_intersect($fileProperties, $schema->required) !== [];
    }

    private function schemaHasFileProperties(Schema $schema): bool
    {
        if ($schema->format === 'binary') {
            return true;
        }

        if ($schema->properties !== null && $schema->properties !== []) {
            foreach ($schema->properties as $property) {
                if (! ($property instanceof Schema)) {
                    continue;
                }

                if ($property->format === 'binary') {
                    return true;
                }

                if ($this->schemaHasFileProperties($property)) {
                    return true;
                }
            }
        }

        return $schema->items instanceof Schema && $this->schemaHasFileProperties($schema->items);
    }

    /**
     * @return list<string>
     */
    private function getFilePropertiesFromSchema(Schema $schema): array
    {
        $fileProperties = [];
        if ($schema->properties !== null && $schema->properties !== []) {
            $childFileProperties = [];
            foreach ($schema->properties as $name => $property) {
                if (! ($property instanceof Schema)) {
                    continue;
                }

                if ($property->format === 'binary') {
                    $fileProperties[] = (string) $name;
                }

                $childFileProperties[] = $this->getFilePropertiesFromSchema($property);
            }

            $fileProperties = array_merge($fileProperties, ...$childFileProperties);
        }

        return $fileProperties;
    }
}
