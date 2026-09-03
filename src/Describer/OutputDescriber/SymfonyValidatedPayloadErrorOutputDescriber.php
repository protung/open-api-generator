<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\OutputDescriber;

use cebe\openapi\spec\Schema;
use Override;
use Protung\OpenApiGenerator\Describer\IOFieldDescriber;
use Protung\OpenApiGenerator\Model\Path\IOField;
use Protung\OpenApiGenerator\Model\Path\Output;
use Protung\OpenApiGenerator\Model\Path\Output\SymfonyValidatedPayloadErrorOutput;
use Psl;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionProperty;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Composite;
use Symfony\Component\Validator\Mapping\CascadingStrategy;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Traversable;

use function class_exists;
use function in_array;
use function is_a;
use function is_string;

final class SymfonyValidatedPayloadErrorOutputDescriber implements OutputDescriber
{
    /**
     * The template Symfony's request payload resolver reports a value it could not denormalize with.
     * It is reachable for every property, no matter which constraints the payload declares.
     */
    private const DENORMALIZATION_MESSAGE_TEMPLATE = 'This value should be of type {{ type }}.';

    private ValidatorInterface $validator;

    private IOFieldDescriber $ioFieldDescriber;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator        = $validator;
        $this->ioFieldDescriber = new IOFieldDescriber();
    }

    #[Override]
    public function describe(Output $output): Schema
    {
        $output = Psl\Type\instance_of(SymfonyValidatedPayloadErrorOutput::class)->coerce($output);

        $propertyPaths = $this->collectPropertyPaths($output->className(), '', []);

        // An empty enum is not a valid schema value, so a payload nothing could be read from is described
        // the same way as one whose paths can not be enumerated: as a plain string.
        $propertyPathField = IOField::stringField('propertyPath');
        if ($propertyPaths !== null && $propertyPaths !== []) {
            $propertyPathField->withPossibleValues($propertyPaths);
        }

        $templateField = IOField::stringField('template');
        if ($output->describesMessageTemplates()) {
            $messageTemplates = $this->collectMessageTemplates($output->className(), $output->validationGroups(), []);
            if ($messageTemplates !== []) {
                $templateField->withPossibleValues($messageTemplates);
            }
        }

        $violations = IOField::arrayField(
            'violations',
            IOField::objectField(
                'violation',
                $propertyPathField,
                IOField::stringField('title'),
                $templateField,
                // An open map of message placeholders, described as a free form object.
                IOField::objectField('parameters'),
            ),
        );

        // A payload which can not be parsed at all never reaches validation and is always answered with a
        // 400 carrying no violations, so only there can the member be missing. Any other status code this
        // document is returned with is reachable through a validation failure alone.
        if ($output->statusCode() === 400) {
            $violations->asOptional();
        }

        $schema = $this->ioFieldDescriber->describeFields(
            [
                IOField::stringField('type'),
                IOField::stringField('title'),
                IOField::integerField('status'),
                IOField::stringField('detail'),
                $violations,
            ],
        );

        $schema->example = $this->describeExample($output, $propertyPaths);

        return $schema;
    }

    #[Override]
    public function supports(Output $output): bool
    {
        return $output instanceof SymfonyValidatedPayloadErrorOutput;
    }

    /**
     * @param class-string       $className
     * @param list<class-string> $visitedClasses
     *
     * @return list<string>|null NULL when the paths can not be enumerated in full. Describing them as an enum
     *                           would then claim a closed set the endpoint can produce a path outside of.
     */
    private function collectPropertyPaths(string $className, string $prefix, array $visitedClasses): array|null
    {
        if (in_array($className, $visitedClasses, true)) {
            // A payload cascading into itself produces paths of unbounded depth.
            return null;
        }

        $classMetadata = $this->validator->getMetadataFor($className);
        if (! $classMetadata instanceof ClassMetadataInterface) {
            return null;
        }

        $visitedClasses[] = $className;

        $paths = [];
        foreach ($this->payloadProperties($className, $classMetadata) as $propertyName) {
            $paths[] = $prefix . $propertyName;

            if (! $this->isCascaded($classMetadata, $propertyName)) {
                continue;
            }

            $nestedClassName = $this->cascadedClassName($className, $propertyName);
            if ($nestedClassName === null) {
                // Cascading into a collection produces indexed paths such as "items[0].name".
                return null;
            }

            $nestedPaths = $this->collectPropertyPaths(
                $nestedClassName,
                $prefix . $propertyName . '.',
                $visitedClasses,
            );

            if ($nestedPaths === null) {
                return null;
            }

            $paths = Psl\Vec\concat($paths, $nestedPaths);
        }

        return $paths;
    }

    /**
     * The denormalizer can fail on any property it writes and not only on the constrained ones, so the
     * properties the payload is built from are described next to the ones carrying constraints. Validation
     * groups deliberately do not narrow this down, they scope which constraints run and not which properties
     * a malformed value can be reported for.
     *
     * @param class-string $className
     *
     * @return list<string>
     */
    private function payloadProperties(string $className, ClassMetadataInterface $classMetadata): array
    {
        $reflectionClass = new ReflectionClass($className);

        $propertyNames = [];
        foreach ($reflectionClass->getConstructor()?->getParameters() ?? [] as $parameter) {
            $propertyNames[] = $parameter->getName();
        }

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $propertyNames[] = $property->getName();
        }

        return Psl\Vec\unique(Psl\Vec\concat($propertyNames, $classMetadata->getConstrainedProperties()));
    }

    private function isCascaded(ClassMetadataInterface $classMetadata, string $propertyName): bool
    {
        if (! $classMetadata->hasPropertyMetadata($propertyName)) {
            return false;
        }

        foreach ($classMetadata->getPropertyMetadata($propertyName) as $propertyMetadata) {
            if ($propertyMetadata->getCascadingStrategy() === CascadingStrategy::CASCADE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param class-string $className
     *
     * @return class-string|null NULL when the cascade does not lead to a single known class, which covers
     *                           collections as well as union and untyped properties.
     */
    private function cascadedClassName(string $className, string $propertyName): string|null
    {
        $reflectionClass = new ReflectionClass($className);
        if (! $reflectionClass->hasProperty($propertyName)) {
            return null;
        }

        $type = $reflectionClass->getProperty($propertyName)->getType();
        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $nestedClassName = $type->getName();
        if (! class_exists($nestedClassName) || is_a($nestedClassName, Traversable::class, true)) {
            return null;
        }

        return $nestedClassName;
    }

    /**
     * @param class-string       $className
     * @param list<string>       $validationGroups
     * @param list<class-string> $visitedClasses
     *
     * @return list<string>
     */
    private function collectMessageTemplates(string $className, array $validationGroups, array $visitedClasses): array
    {
        if (in_array($className, $visitedClasses, true)) {
            return [];
        }

        $classMetadata = $this->validator->getMetadataFor($className);
        if (! $classMetadata instanceof ClassMetadataInterface) {
            return [];
        }

        $visitedClasses[] = $className;

        $messageTemplates = [self::DENORMALIZATION_MESSAGE_TEMPLATE];
        foreach ($this->payloadProperties($className, $classMetadata) as $propertyName) {
            if (! $classMetadata->hasPropertyMetadata($propertyName)) {
                continue;
            }

            foreach ($classMetadata->getPropertyMetadata($propertyName) as $propertyMetadata) {
                $messageTemplates = Psl\Vec\concat(
                    $messageTemplates,
                    $this->messageTemplatesOf($this->constraintsOf($propertyMetadata, $validationGroups)),
                );
            }

            $nestedClassName = $this->cascadedClassName($className, $propertyName);
            if ($nestedClassName === null || ! $this->isCascaded($classMetadata, $propertyName)) {
                continue;
            }

            $messageTemplates = Psl\Vec\concat(
                $messageTemplates,
                $this->collectMessageTemplates($nestedClassName, $validationGroups, $visitedClasses),
            );
        }

        return Psl\Vec\unique($messageTemplates);
    }

    /**
     * @param list<string> $validationGroups
     *
     * @return list<Constraint>
     */
    private function constraintsOf(PropertyMetadataInterface $propertyMetadata, array $validationGroups): array
    {
        if ($validationGroups === []) {
            return Psl\Vec\values($propertyMetadata->getConstraints());
        }

        return Psl\Vec\values(
            Psl\Vec\flat_map($validationGroups, $propertyMetadata->findConstraints(...)),
        );
    }

    /**
     * Message templates are declared as public properties on the constraint, so a constraint configured with a
     * custom message is read as configured. Every message a constraint declares is described, including the ones
     * its configuration can never reach, which keeps the described list a superset of what the endpoint sends.
     *
     * @param list<Constraint> $constraints
     *
     * @return list<string>
     */
    private function messageTemplatesOf(array $constraints): array
    {
        $messageTemplates = [];
        foreach ($constraints as $constraint) {
            if ($constraint instanceof Composite) {
                $messageTemplates = Psl\Vec\concat(
                    $messageTemplates,
                    $this->messageTemplatesOf(Psl\Vec\values($constraint->getNestedConstraints())),
                );
            }

            foreach ((new ReflectionObject($constraint))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                $propertyName = $property->getName();
                if ($property->isStatic() || ! $this->isMessageProperty($propertyName)) {
                    continue;
                }

                if (! $property->isInitialized($constraint)) {
                    continue;
                }

                $messageTemplate = $property->getValue($constraint);
                if (! is_string($messageTemplate) || $messageTemplate === '') {
                    continue;
                }

                $messageTemplates[] = $messageTemplate;
            }
        }

        return Psl\Vec\values($messageTemplates);
    }

    private function isMessageProperty(string $propertyName): bool
    {
        return $propertyName === 'message' || Psl\Str\ends_with($propertyName, 'Message');
    }

    /**
     * @param list<string>|null $propertyPaths
     *
     * @return array<string, mixed>
     */
    private function describeExample(SymfonyValidatedPayloadErrorOutput $output, array|null $propertyPaths): array
    {
        $propertyPath = $propertyPaths[0] ?? 'propertyName';

        return [
            'type' => 'https://symfony.com/errors/validation',
            'title' => 'Validation Failed',
            'status' => $output->statusCode(),
            'detail' => $propertyPath . ': This value is not valid.',
            'violations' => [
                [
                    'propertyPath' => $propertyPath,
                    'title' => 'This value is not valid.',
                    'template' => self::DENORMALIZATION_MESSAGE_TEMPLATE,
                    'parameters' => ['{{ type }}' => 'string'],
                ],
            ],
        ];
    }
}
