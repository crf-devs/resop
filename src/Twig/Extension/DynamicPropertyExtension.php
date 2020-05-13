<?php


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

    public function dynamicPropertyValue($value, array $propertyDefinition): string
    {
        if (is_bool($value)) {
            return $this->translator->trans(sprintf('common.%s', $value ? 'yes' : 'no'));
        }

        if (\in_array($propertyDefinition['type'], [DynamicPropertiesType::TYPE_CHOICE, DynamicPropertiesType::TYPE_CHOICE_WITH_OTHER], true)) {
            return array_flip($propertyDefinition['choices'])[$value] ?? $value;
        }

        if (!\is_string($value)) {
            return $value;
        }

        if (\strlen($value) <= 75) {
            return $value;
        }

        return substr($value, 0, 72).'...';
    }
}
