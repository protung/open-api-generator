<?php

declare(strict_types=1);

namespace Protung\OpenApiGenerator\Describer\Form\NameResolver;

use Override;
use Protung\OpenApiGenerator\Assert\Assert;
use Symfony\Component\Form\FormInterface;

use function array_shift;

final class FlatArray implements \Protung\OpenApiGenerator\Describer\Form\FlatNameResolver
{
    use FlatNameResolver;

    #[Override]
    public function getPropertyName(FormInterface $form): string
    {
        $names = $this->namesFromForm($form);

        $name = array_shift($names);
        Assert::notNull($name);

        return $this->fromArray($name, $names, $form->getConfig());
    }
}
