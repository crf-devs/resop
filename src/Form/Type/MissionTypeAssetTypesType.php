<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionTypeAssetTypesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'common.type',
                'choices' => CommissionableAssetType::TYPES,
                'placeholder' => '',
                'row_attr' => ['class' => 'row'],
                'label_attr' => ['class' => 'col-sm-2'],
                'attr' => ['class' => 'col-sm-6'],
            ])
            ->add('number', IntegerType::class, [
                'label' => 'common.number',
                'row_attr' => ['class' => 'row'],
                'label_attr' => ['class' => 'col-sm-2'],
                'attr' => ['class' => 'col-sm-4'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'row_attr' => [
                'class' => 'shadow p-3 mt-3 rounded col-sm-10 mx-auto',
            ],
        ]);
    }
}
