<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Form\Type\DynamicPropertiesType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DynamicPropertyExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('dynamicPropertyValue', [$this, 'dynamicPropertyValue']),
        ];
    }

    /**
     * @param bool|string|int $value
     */
    public function dynamicPropertyValue($value, array $propertyDefinition): string
    {
        if (\in_array($propertyDefinition['type'], [DynamicPropertiesType::TYPE_CHOICE, DynamicPropertiesType::TYPE_CHOICE_WITH_OTHER], true)) {
            return array_flip($propertyDefinition['choices'] ?? [])[$value] ?? $value;
        }

        if (DynamicPropertiesType::TYPE_BOOLEAN === $propertyDefinition['type']) {
            // false value will be sent by `default` twig filter as an empty string or an hyphen
            if (\in_array($value, ['', '-'], true)) {
                $value = false;
            }

            return $this->translator->trans(sprintf('common.%s', (bool) $value ? 'yes' : 'no'));
        }

        return (string) $value;
    }
}
