<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\InputDescriber;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use InvalidArgumentException;
use Override;
use Protung\OpenApiGenerator\Assert\Assert;
use Protung\OpenApiGenerator\Describer\SymfonyValidatorConstraintsDescriber;
use Protung\OpenApiGenerator\Model\Path\Input;
use Protung\OpenApiGenerator\Model\Path\Input\SymfonyMappedPayloadInput;
use Psl;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @todo support nested payload objects and collections of them. A payload holding another payload, or a
 *       list of them, can not be described at all today: the whole body falls back to being written by
 *       hand with BodyInput::withIOFields() as soon as one property is not a scalar.
 */
final class SymfonyMappedPayloadInputDescriber implements InputDescriber
{
    private const CONTENT_TYPE_APPLICATION_JSON = 'application/json';

    private ValidatorInterface $validator;

    private SymfonyValidatorConstraintsDescriber $constraintsDescriber;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator            = $validator;
        $this->constraintsDescriber = new SymfonyValidatorConstraintsDescriber();
    }

    #[Override]
    public function describe(Input $input, Operation $operation, string $httpMethod): void
    {
        $input = Psl\Type\instance_of(SymfonyMappedPayloadInput::class)->coerce($input);

        Assert::notSame($httpMethod, 'GET', 'Body input is not allowed in GET requests.');

        $operation->requestBody = new RequestBody(
            [
                'required' => true,
                'content' => [
                    self::CONTENT_TYPE_APPLICATION_JSON => new MediaType(
                        ['schema' => $this->describePayload($input->className())],
                    ),
                ],
            ],
        );
    }

    #[Override]
    public function supports(Input $input): bool
    {
        return $input instanceof SymfonyMappedPayloadInput;
    }

    /**
     * @param class-string $className
     */
    private function describePayload(string $className): Schema
    {
        $classMetadata = $this->validator->getMetadataFor($className);

        $properties = [];
        $required   = [];
        foreach ((new ReflectionClass($className))->getConstructor()?->getParameters() ?? [] as $parameter) {
            $propertyName              = $parameter->getName();
            $properties[$propertyName] = $this->describeProperty($className, $parameter, $classMetadata);

            if (! $this->isRequired($parameter)) {
                continue;
            }

            $required[] = $propertyName;
        }

        $schema = ['type' => Type::OBJECT, 'properties' => $properties];
        if ($required !== []) {
            // An empty required list is not a valid schema value, so it is left out entirely.
            $schema['required'] = $required;
        }

        return new Schema($schema);
    }

    /**
     * A payload property is only filled in from the request, so a parameter which can be left out,
     * either by being nullable or by having a default, is not required.
     */
    private function isRequired(ReflectionParameter $parameter): bool
    {
        return ! $parameter->isDefaultValueAvailable() && $parameter->getType()?->allowsNull() !== true;
    }

    /**
     * @param class-string $className
     */
    private function describeProperty(
        string $className,
        ReflectionParameter $parameter,
        mixed $classMetadata,
    ): Schema {
        $schema = new Schema(['type' => $this->describeType($className, $parameter)]);
        if ($parameter->getType()?->allowsNull() === true) {
            $schema->nullable = true;
        }

        $constraints = $this->constraintsOf($classMetadata, $parameter->getName());

        $this->constraintsDescriber->describe($constraints, $schema, false);
        $this->describeNotBlank($schema, $constraints);

        return $schema;
    }

    /**
     * @param class-string $className
     */
    private function describeType(string $className, ReflectionParameter $parameter): string
    {
        $parameterType = $parameter->getType();
        if ($parameterType instanceof ReflectionNamedType) {
            $type = match ($parameterType->getName()) {
                'string' => Type::STRING,
                'int' => Type::INTEGER,
                'bool' => Type::BOOLEAN,
                'float' => Type::NUMBER,
                default => null,
            };

            if ($type !== null) {
                return $type;
            }
        }

        throw new InvalidArgumentException(
            Psl\Str\format(
                'Can not describe property "%s" of payload "%s", only string, int, bool and float are supported, "%s" given. Describe this body with BodyInput::withIOFields() instead.',
                $parameter->getName(),
                $className,
                (string) $parameterType,
            ),
        );
    }

    /**
     * @return list<Constraint>
     */
    private function constraintsOf(mixed $classMetadata, string $propertyName): array
    {
        if (! $classMetadata instanceof ClassMetadataInterface || ! $classMetadata->hasPropertyMetadata($propertyName)) {
            return [];
        }

        return Psl\Vec\values(
            Psl\Vec\flat_map(
                $classMetadata->getPropertyMetadata($propertyName),
                static fn (PropertyMetadataInterface $propertyMetadata): array => $propertyMetadata->getConstraints(),
            ),
        );
    }

    /**
     * A blank value being rejected means the shortest string the property accepts is one character long.
     * Where a Length already declares a longer minimum that one stays, being the stricter of the two.
     *
     * @param list<Constraint> $constraints
     */
    private function describeNotBlank(Schema $schema, array $constraints): void
    {
        if ($schema->type !== Type::STRING) {
            return;
        }

        foreach ($constraints as $constraint) {
            if (! $constraint instanceof NotBlank) {
                continue;
            }

            if ($schema->minLength !== null && $schema->minLength >= 1) {
                continue;
            }

            $schema->minLength = 1;
        }
    }
}
