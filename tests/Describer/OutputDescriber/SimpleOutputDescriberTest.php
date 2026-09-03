<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\OutputDescriber;

use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Describer\OutputDescriber\SimpleOutputDescriber;
use Protung\OpenApiGenerator\Model\Path\Output\SymfonyValidationErrorOutput;
use Psl\Json;

final class SimpleOutputDescriberTest extends TestCase
{
    public function testDescribeSymfonyValidationErrorOutput(): void
    {
        $schema = (new SimpleOutputDescriber())->describe(SymfonyValidationErrorOutput::create(400));

        // "violations" is only sent for a payload which parsed but failed validation,
        // a payload which could not be parsed at all gets the same document without it.
        self::assertSame(['type', 'title', 'status', 'detail'], $schema->required);

        $expectedSchema = <<<'JSON'
        {
            "required": ["type", "title", "status", "detail"],
            "type": "object",
            "properties": {
                "type": {"type": "string"},
                "title": {"type": "string"},
                "status": {"type": "integer"},
                "detail": {"type": "string"},
                "violations": {
                    "type": "array",
                    "items": {
                        "required": ["propertyPath", "title", "template", "parameters"],
                        "type": "object",
                        "properties": {
                            "propertyPath": {"type": "string"},
                            "title": {"type": "string"},
                            "template": {"type": "string"},
                            "parameters": {"type": "object"}
                        }
                    }
                }
            },
            "example": {
                "type": "https://symfony.com/errors/validation",
                "title": "Validation Failed",
                "status": 400,
                "detail": "pairingCode: This value is not valid.",
                "violations": [
                    {
                        "propertyPath": "pairingCode",
                        "title": "This value is not valid.",
                        "template": "This value should be of type {{ type }}.",
                        "parameters": {"{{ type }}": "string"}
                    }
                ]
            }
        }
        JSON;

        self::assertJsonStringEqualsJsonString(
            $expectedSchema,
            Json\encode($schema->getSerializableData()),
        );
    }

    public function testDescribeSymfonyValidationErrorOutputRequiresViolationsForAnyOtherStatusCode(): void
    {
        $schema = (new SimpleOutputDescriber())->describe(SymfonyValidationErrorOutput::create());

        // 422 is only ever reached through a validation failure, so the violations are always sent.
        self::assertSame(['type', 'title', 'status', 'detail', 'violations'], $schema->required);
    }
}
