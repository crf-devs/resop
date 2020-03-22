<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceWithOtherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('choice', ChoiceType::class, array_merge($options, ['label' => false]))
            ->add('other', TextType::class, ['label' => false])
        ;

        $builder->addModelTransformer(new CallbackTransformer(
            function ($transformed) use ($options) {
                if (in_array($transformed, $options['choices'], true)) {
                    return [
                        'choice' => $transformed,
                        'other' => null,
                    ];
                }

                return [
                    'choice' => '-',
                    'other' => $transformed,
                ];
            },
            static function ($reverseTransformed) {
                if (!empty($reverseTransformed['choice'] && '-' !== $reverseTransformed['choice'])) {
                    return $reverseTransformed['choice'];
                }
                if (empty($reverseTransformed['other'])) {
                    return '';
                }

                return $reverseTransformed['other'];
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Borrowed form Symfony\Component\Form\Extension\Core\Type\ChoiceType :)
        $emptyData = function (Options $options) {
            if ($options['expanded'] && !$options['multiple']) {
                return null;
            }

            if ($options['multiple']) {
                return [];
            }

            return '';
        };

        $placeholderDefault = function (Options $options) {
            return $options['required'] ? null : '';
        };

        $compound = function (Options $options) {
            return $options['expanded'];
        };

        $resolver->setDefaults([
            'multiple' => false,
            'expanded' => false,
            'choices' => [],
            'choice_loader' => null,
            'choice_label' => null,
            'choice_name' => null,
            'choice_value' => null,
            'choice_attr' => null,
            'preferred_choices' => [],
            'group_by' => null,
            'empty_data' => $emptyData,
            'placeholder' => $placeholderDefault,
            'error_bubbling' => false,
            'compound' => $compound,
            // The view data is always a string or an array of strings,
            // even if the "data" option is manually set to an object.
            // See https://github.com/symfony/symfony/pull/5582
            'data_class' => null,
            'choice_translation_domain' => true,
            'trim' => false,
        ]);
    }
}
