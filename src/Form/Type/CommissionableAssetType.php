<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\CommissionableAsset;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CommissionableAssetType extends AbstractType
{
    private const TYPES = [
        'VL - Véhicule léger' => 'VL',
        'VPSP - Véhicule de premiers secours' => 'VPSP',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => self::TYPES,
            ])
            ->add('name', TextType::class)
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommissionableAsset::class,
        ]);
    }
}
