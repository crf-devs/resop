<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class DynamicPropertiesType extends AbstractType
{
    public const TYPE_SMALL_TEXT = 'smallText';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_NUMBER = 'number';
    public const TYPE_TEXT = 'text';
    public const TYPE_CHOICE = 'choice';
    public const TYPE_CHOICE_WITH_OTHER = 'choice_with_other';

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['config'] as $property) {
            if (true === $property['hidden']) {
                continue;
            }

            $options = [
                'label' => $property['label'],
                'help' => $property['help'] ?: '',
                'required' => $property['required'],
            ];

            switch ($property['type']) {
                case self::TYPE_SMALL_TEXT:
                    $formClass = TextType::class;
                    break;
                case self::TYPE_TEXT:
                    $formClass = TextareaType::class;
                    break;
                case self::TYPE_NUMBER:
                    $formClass = IntegerType::class;
                    break;
                case self::TYPE_BOOLEAN:
                    $formClass = ChoiceType::class;
                    $options['expanded'] = true;
                    $options['choices'] = ['common.yes' => true, 'common.no' => false];
                    break;
                case self::TYPE_CHOICE_WITH_OTHER:
                    $property['choices'][$this->translator->trans('common.other')] = '-';
                    $options['attr'] = ['class' => 'js-choice-with-other'];
                    // no break
                case self::TYPE_CHOICE:
                    $formClass = self::TYPE_CHOICE === $property['type'] ? ChoiceType::class : ChoiceWithOtherType::class;
                    $options['expanded'] = true;
                    $options['placeholder'] = false;

                    if (!isset($property['choices']) || !\is_array($property['choices']) || \count($property['choices']) < 2) {
                        throw new \InvalidArgumentException('Invalid property "%s". Key "choices" is mandatory and at least two choices should be provided.');
                    }
                    $options['choices'] = $property['choices'];
                    break;
                default:
                    throw new \InvalidArgumentException('Unsupported property.type');
            }

            $options['help_html'] = true;

            $builder->add($property['key'], $formClass, $options);
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($options['config'] as $property) {
            if (!isset($property['container_class'])) {
                continue;
            }

            $view->children[$property['key']]->vars['container_class'] = $property['container_class'];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['config' => []]);
    }
}
