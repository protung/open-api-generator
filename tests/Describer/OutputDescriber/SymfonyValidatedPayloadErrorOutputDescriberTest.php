<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Tests\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use PHPUnit\Framework\TestCase;
use Protung\OpenApiGenerator\Describer\OutputDescriber\SymfonyValidatedPayloadErrorOutputDescriber;
use Protung\OpenApiGenerator\Model\Path\Output\SymfonyValidatedPayloadErrorOutput;
use Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures\CollectionPayload;
use Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures\GroupedPayload;
use Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures\NestedPayload;
use Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures\PairRequest;
use Protung\OpenApiGenerator\Tests\Describer\OutputDescriber\Fixtures\RecursivePayload;
use Psl;
use Psl\Json;
use Symfony\Component\Validator\ValidatorBuilder;

final class SymfonyValidatedPayloadErrorOutputDescriberTest extends TestCase
{
    public function testPropertyPathsAreNotDescribedWhenThePayloadCascadesIntoACollection(): void
    {
        // Violations of a collection carry an indexed path such as "pairs[0].pairingCode", so the reachable
        // paths can not be listed and describing any of them would claim a closed set which is not closed.
        $schema = $this->describe(SymfonyValidatedPayloadErrorOutput::forClass(CollectionPayload::class));

        self::assertNull($this->propertyPathEnum($schema));
    }

    public function testPropertyPathsAreNotDescribedWhenThePayloadCascadesIntoItself(): void
    {
        $schema = $this->describe(SymfonyValidatedPayloadErrorOutput::forClass(RecursivePayload::class));

        self::assertNull($this->propertyPathEnum($schema));
    }

    public function testPropertyPathsAreDescribedForTheMappedPayload(): void
    {
        $schema = $this->describe(SymfonyValidatedPayloadErrorOutput::forClass(PairRequest::class));

        // "retryCount" carries no constraint but the denormalizer can still report a type violation for it.
        self::assertSame(['pairingCode', 'retryCount'], $this->propertyPathEnum($schema));
    }

    public function testCascadedPropertyPathsAreDescribedWithTheirPrefix(): void
    {
        $schema = $this->describe(SymfonyValidatedPayloadErrorOutput::forClass(NestedPayload::class));

        self::assertSame(
            ['name', 'pair', 'pair.pairingCode', 'pair.retryCount'],
            $this->propertyPathEnum($schema),
        );
    }

    public function testMessageTemplatesAreOnlyDescribedWhenAskedTo(): void
    {
        $schema = $this->describe(SymfonyValidatedPayloadErrorOutput::forClass(PairRequest::class));

        self::assertNull($this->violationEnum($schema, 'template'));
    }

    public function testMessageTemplatesAreReadFromTheDeclaredConstraints(): void
    {
        $schema = $this->describe(
            SymfonyValidatedPayloadErrorOutput::forClass(PairRequest::class)->withMessageTemplates(),
        );

        self::assertSame(
            [
                'This value should be of type {{ type }}.',
                'This value should not be blank.',
                // Symfony keeps both plural forms in a single template, so that is what a violation reports.
                'This value is too long. It should have {{ limit }} character or less.|This value is too long. It should have {{ limit }} characters or less.',
                'This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more.',
                'This value should have exactly {{ limit }} character.|This value should have exactly {{ limit }} characters.',
                'This value does not match the expected {{ charset }} charset.',
            ],
            $this->violationEnum($schema, 'template'),
        );
    }

    public function testMessageTemplatesAreRestrictedToTheGivenValidationGroups(): void
    {
        $schema = $this->describe(
            SymfonyValidatedPayloadErrorOutput::forClass(GroupedPayload::class)
                ->withMessageTemplates()
                ->withValidationGroups('strict'),
        );

        self::assertSame(
            [
                'This value should be of type {{ type }}.',
                'The nickname is required.',
            ],
            $this->violationEnum($schema, 'template'),
        );
    }

    public function testMessageTemplatesOfEveryGroupAreDescribedWhenNoGroupIsGiven(): void
    {
        $schema = $this->describe(
            SymfonyValidatedPayloadErrorOutput::forClass(GroupedPayload::class)->withMessageTemplates(),
        );

        self::assertSame(
            [
                'This value should be of type {{ type }}.',
                'The name is required.',
                'The nickname is required.',
            ],
            $this->violationEnum($schema, 'template'),
        );
    }

    public function testViolationsAreRequiredUnlessTheDocumentIsReturnedWithA400(): void
    {
        self::assertSame(
            ['type', 'title', 'status', 'detail', 'violations'],
            $this->describe(SymfonyValidatedPayloadErrorOutput::forClass(PairRequest::class))->required,
        );

        self::assertSame(
            ['type', 'title', 'status', 'detail'],
            $this->describe(
                SymfonyValidatedPayloadErrorOutput::forClass(PairRequest::class)->withStatusCode(400),
            )->required,
        );
    }

    public function testExampleUsesAPropertyOfTheMappedPayload(): void
    {
        $schema = $this->describe(SymfonyValidatedPayloadErrorOutput::forClass(PairRequest::class));

        self::assertJsonStringEqualsJsonString(
            <<<'JSON'
            {
                "type": "https://symfony.com/errors/validation",
                "title": "Validation Failed",
                "status": 422,
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
            JSON,
            Json\encode($schema->example),
        );
    }

    private function describe(SymfonyValidatedPayloadErrorOutput $output): Schema
    {
        $validator = (new ValidatorBuilder())->enableAttributeMapping()->getValidator();

        return (new SymfonyValidatedPayloadErrorOutputDescriber($validator))->describe($output);
    }

    /**
     * @return list<string>|null
     */
    private function propertyPathEnum(Schema $schema): array|null
    {
        return $this->violationEnum($schema, 'propertyPath');
    }

    /**
     * @return list<string>|null
     */
    private function violationEnum(Schema $schema, string $member): array|null
    {
        $violationShape = Psl\Type\shape(
            [
                'properties' => Psl\Type\shape(
                    [
                        'violations' => Psl\Type\shape(
                            [
                                'items' => Psl\Type\shape(
                                    [
                                        'properties' => Psl\Type\dict(
                                            Psl\Type\string(),
                                            Psl\Type\shape(
                                                ['enum' => Psl\Type\optional(Psl\Type\vec(Psl\Type\string()))],
                                                true,
                                            ),
                                        ),
                                    ],
                                    true,
                                ),
                            ],
                            true,
                        ),
                    ],
                    true,
                ),
            ],
            true,
        );

        $described = $violationShape->coerce(Json\decode(Json\encode($schema->getSerializableData())));

        return $described['properties']['violations']['items']['properties'][$member]['enum'] ?? null;
    }
}
