<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningDynamicFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['config'] as $property) {
            if (true === $property['hidden']) {
                continue;
            }

            if (DynamicPropertiesType::TYPE_BOOLEAN !== $property['type']) {
                throw new \InvalidArgumentException('Dynamic filters only accepts booleans.');
            }

            $builder->add(
                $property['key'],
                ChoiceType::class,
                [
                    'required' => false,
                    'label' => $property['columnLabel'] ?? $property['label'],
                    'placeholder' => 'organization.planning.booleanFilterPlaceholder',
                    'choices' => [
                        'common.no' => 0,
                        'common.yes' => 1,
                    ],
                    'attr' => ['class' => 'dynamic_planning_filter'],
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['config' => []]);
    }
}
