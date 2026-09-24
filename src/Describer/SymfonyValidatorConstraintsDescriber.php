<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Psl;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\DivisibleBy;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Unique;

use function implode;
use function number_format;

/**
 * Writes what Symfony Validator constraints say about a value into the schema describing it.
 *
 * Knows nothing about where the constraints were read from, so a form field and a property of a mapped
 * request payload carrying the same constraint can not end up documented differently.
 */
final class SymfonyValidatorConstraintsDescriber
{
    private const CLOSING_DELIMITERS = ['(' => ')', '[' => ']', '{' => '}', '<' => '>'];

    /**
     * @param array<Constraint> $constraints
     * @param bool              $describesCollection Whether the schema describes a collection, which decides
     *                                               if Count limits the items or the properties.
     */
    public function describe(array $constraints, Schema $schema, bool $describesCollection): void
    {
        foreach ($constraints as $constraint) {
            switch (true) {
                case $constraint instanceof NotBlank:
                    if ($constraint->allowNull) {
                        $schema->nullable = true;
                    }

                    break;
                case $constraint instanceof NotNull:
                    // Nullability is decided by the caller, which knows more about the value than the constraint.
                    break;
                case $constraint instanceof All:
                    // All validates each item, not the collection holding them.
                    $items = $schema->items;
                    if ($items instanceof Schema) {
                        $this->describe($constraint->getNestedConstraints(), $items, $items->type === Type::ARRAY);
                    }

                    break;
                case $constraint instanceof Sequentially || $constraint instanceof Compound:
                    // The only composites whose nested constraints all apply to the value itself. Those of AtLeastOneOf
                    // (any one of them), Collection (one per key) and When (only under a condition) are left out.
                    $this->describe($constraint->getNestedConstraints(), $schema, $describesCollection);
                    break;
                case $constraint instanceof Count && $describesCollection:
                    if ($constraint->min !== null) {
                        $schema->minItems = Psl\Type\int()->coerce($constraint->min);
                    }

                    if ($constraint->max !== null) {
                        $schema->maxItems = Psl\Type\int()->coerce($constraint->max);
                    }

                    break;
                case $constraint instanceof Count && ! $describesCollection:
                    if ($constraint->min !== null) {
                        $schema->minProperties = Psl\Type\int()->coerce($constraint->min);
                    }

                    if ($constraint->max !== null) {
                        $schema->maxProperties = Psl\Type\int()->coerce($constraint->max);
                    }

                    break;
                case $constraint instanceof DivisibleBy:
                    if ($constraint->value !== null && Psl\Type\num()->matches($constraint->value)) {
                        $schema->multipleOf = $constraint->value;
                    }

                    break;
                case $constraint instanceof Email:
                    $schema->format = 'email';
                    break;
                case $constraint instanceof GreaterThan:
                    if ($constraint->value !== null && Psl\Type\num()->matches($constraint->value)) {
                        $schema->minimum          = $constraint->value;
                        $schema->exclusiveMinimum = true;
                    }

                    break;
                case $constraint instanceof GreaterThanOrEqual:
                    if ($constraint->value !== null && Psl\Type\num()->matches($constraint->value)) {
                        $schema->minimum = $constraint->value;
                    }

                    break;
                case $constraint instanceof Length:
                    if ($constraint->min !== null) {
                        $schema->minLength = Psl\Type\int()->coerce($constraint->min);
                    }

                    if ($constraint->max !== null) {
                        $schema->maxLength = Psl\Type\int()->coerce($constraint->max);
                    }

                    break;
                case $constraint instanceof LessThan:
                    if ($constraint->value !== null && Psl\Type\num()->matches($constraint->value)) {
                        $schema->maximum          = $constraint->value;
                        $schema->exclusiveMaximum = true;
                    }

                    break;
                case $constraint instanceof LessThanOrEqual:
                    if ($constraint->value !== null && Psl\Type\num()->matches($constraint->value)) {
                        $schema->maximum = $constraint->value;
                    }

                    break;
                case $constraint instanceof Range:
                    if ($constraint->min !== null && Psl\Type\num()->matches($constraint->min)) {
                        $schema->minimum = $constraint->min;
                    }

                    if ($constraint->max !== null && Psl\Type\num()->matches($constraint->max)) {
                        $schema->maximum = $constraint->max;
                    }

                    break;
                case $constraint instanceof Unique:
                    $schema->uniqueItems = true;
                    break;
                case $constraint instanceof Regex:
                    $pattern = self::patternOf($constraint);
                    if ($pattern !== null) {
                        $schema->pattern = $pattern;
                    }

                    break;
                case $constraint instanceof File:
                    if ($constraint->mimeTypes !== '' && $constraint->mimeTypes !== []) {
                        $schema->description = SpecificationDescriber::updateDescription(
                            $schema->description,
                            Psl\Str\format(
                                'Allowed mime types: %s',
                                implode(', ', Psl\Type\vec(Psl\Type\string())->coerce((array) $constraint->mimeTypes)),
                            ),
                        );
                    }

                    if ($constraint->maxSize !== null) {
                        $schema->description = SpecificationDescriber::updateDescription(
                            $schema->description,
                            Psl\Str\format('Allowed max file size: %s', $this->humanReadableFileSize($constraint->maxSize)),
                        );
                    }

                    if ($constraint instanceof Image) {
                        if ($constraint->minWidth !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed minimum width is %dpx', Psl\Type\int()->coerce($constraint->minWidth)),
                            );
                        }

                        if ($constraint->minHeight !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed minimum height is %dpx', Psl\Type\int()->coerce($constraint->minHeight)),
                            );
                        }

                        if ($constraint->maxWidth !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed maximum width is %dpx', Psl\Type\int()->coerce($constraint->maxWidth)),
                            );
                        }

                        if ($constraint->maxHeight !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed maximum height is %dpx', Psl\Type\int()->coerce($constraint->maxHeight)),
                            );
                        }
                    }

                    break;
            }
        }
    }

    /**
     * The pattern of a Regex constraint without its delimiters, as a schema expects it.
     *
     * There is none when the constraint rejects what matches, or when a modifier other than "u" and "D" changes
     * what the pattern matches ("i", "m", "s", "x", ...), because a documented pattern stricter than the
     * validation would have clients reject values the API accepts.
     */
    private static function patternOf(Regex $constraint): string|null
    {
        $pattern = Psl\Type\nullable(Psl\Type\string())->coerce($constraint->pattern);
        if ($pattern === null || $pattern === '' || $constraint->match !== true) {
            return null;
        }

        $end = Psl\Str\search_last($pattern, self::CLOSING_DELIMITERS[$pattern[0]] ?? $pattern[0]);
        if ($end === null || $end === 0) {
            return null;
        }

        if (! Psl\Regex\matches(Psl\Str\slice($pattern, $end + 1), '/^[uD]*$/')) {
            return null;
        }

        return Psl\Str\slice($pattern, 1, $end - 1);
    }

    private function humanReadableFileSize(int $size): string
    {
        if ($size >= 1_048_576) {
            return Psl\Str\format('%s MB', number_format($size / 1_048_576, $size % 1_048_576 === 0 ? 0 : 3));
        }

        if ($size >= 1_024) {
            return Psl\Str\format('%s KB', number_format($size / 1_024, $size % 1_024 === 0 ? 0 : 3));
        }

        return Psl\Str\format('%d bytes', $size);
    }
}
