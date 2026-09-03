<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer;

use cebe\openapi\spec\Schema;
use Psl;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Composite;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\DivisibleBy;
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
                case $constraint instanceof Composite:
                    $this->describe($constraint->getNestedConstraints(), $schema, $describesCollection);
                    break;
                case $constraint instanceof Count && $describesCollection:
                    if ($constraint->min !== null) {
                        $schema->minItems = $constraint->min;
                    }

                    if ($constraint->max !== null) {
                        $schema->maxItems = $constraint->max;
                    }

                    break;
                case $constraint instanceof Count && ! $describesCollection:
                    if ($constraint->min !== null) {
                        $schema->minProperties = $constraint->min;
                    }

                    if ($constraint->max !== null) {
                        $schema->maxProperties = $constraint->max;
                    }

                    break;
                case $constraint instanceof DivisibleBy:
                    $schema->multipleOf = Psl\Type\num()->coerce($constraint->value);
                    break;
                case $constraint instanceof GreaterThan:
                    $schema->minimum          = Psl\Type\num()->coerce($constraint->value);
                    $schema->exclusiveMinimum = true;
                    break;
                case $constraint instanceof GreaterThanOrEqual:
                    $schema->minimum = Psl\Type\num()->coerce($constraint->value);
                    break;
                case $constraint instanceof Length:
                    if ($constraint->min !== null) {
                        $schema->minLength = $constraint->min;
                    }

                    if ($constraint->max !== null) {
                        $schema->maxLength = $constraint->max;
                    }

                    break;
                case $constraint instanceof LessThan:
                    $schema->maximum          = Psl\Type\num()->coerce($constraint->value);
                    $schema->exclusiveMaximum = true;
                    break;
                case $constraint instanceof LessThanOrEqual:
                    $schema->maximum = Psl\Type\num()->coerce($constraint->value);
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
                    // we need to remove the delimiters but ignoring the modifiers
                    if ($constraint->pattern !== null) {
                        $schema->pattern = Psl\Str\slice(
                            Psl\Type\non_empty_string()->coerce(Psl\Str\before_last_ci($constraint->pattern, $constraint->pattern[0])),
                            1,
                        );
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
                                Psl\Str\format('Allowed minimum width is %dpx', $constraint->minWidth),
                            );
                        }

                        if ($constraint->minHeight !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed minimum height is %dpx', $constraint->minHeight),
                            );
                        }

                        if ($constraint->maxWidth !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed maximum width is %dpx', $constraint->maxWidth),
                            );
                        }

                        if ($constraint->maxHeight !== null) {
                            $schema->description = SpecificationDescriber::updateDescription(
                                $schema->description,
                                Psl\Str\format('Allowed maximum height is %dpx', $constraint->maxHeight),
                            );
                        }
                    }

                    break;
            }
        }
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
