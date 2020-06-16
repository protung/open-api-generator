<?php

declare(strict_types=1);

namespace Speicher210\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Speicher210\OpenApiGenerator\Assert\Assert;
use Speicher210\OpenApiGenerator\Model\Path\Output;
use Speicher210\OpenApiGenerator\Model\Path\Output\PaginatedOutput;

use function array_fill_keys;
use function array_map;
use function count;
use function reset;

final class PaginatedOutputDescriber implements OutputDescriber
{
    private \Speicher210\OpenApiGenerator\Describer\OutputDescriber $outputDescriber;

    public function __construct(\Speicher210\OpenApiGenerator\Describer\OutputDescriber $outputDescriber)
    {
        $this->outputDescriber = $outputDescriber;
    }

    public function describe(Output $output): Schema
    {
        Assert::isInstanceOf($output, PaginatedOutput::class);

        return new Schema(
            [
                'properties' => [
                    'page' => new Schema(['type' => Type::INTEGER]),
                    'limit' => new Schema(['type' => Type::INTEGER]),
                    'pages' => new Schema(['type' => Type::INTEGER]),
                    'total' => new Schema(['type' => Type::INTEGER]),
                    '_links' => $this->createLinksSchema(),
                    '_embedded' => $this->createEmbeddedSchema($output),
                ],
            ]
        );
    }

    private function createLinksSchema(): Schema
    {
        return new Schema(
            [
                'properties' => array_fill_keys(
                    ['self', 'first', 'last', 'previous', 'next'],
                    new Schema(
                        [
                            'properties' => ['href' => new Schema(['type' => Type::STRING])],
                            'type' => Type::OBJECT,
                        ]
                    )
                ),
                'type' => Type::OBJECT,
                'required' => [
                    'self',
                    'first',
                ],
            ]
        );
    }

    private function createEmbeddedSchema(PaginatedOutput $output): Schema
    {
        $resourcesSchema = new Schema(['type' => Type::ARRAY]);

        $resources = array_map(
            function (Output $resource) {
                return $this->outputDescriber->describe($resource);
            },
            $output->embedded()
        );

        if (count($resources) > 1) {
            $resourcesSchema->items = new Schema(['oneOf' => $resources]);
        } else {
            $resourcesSchema->items = reset($resources);
        }

        return new Schema(
            [
                'type' => Type::OBJECT,
                'properties' => [$output->resourcesKey() => $resourcesSchema],
            ]
        );
    }

    public function supports(Output $output): bool
    {
        return $output instanceof PaginatedOutput;
    }
}
