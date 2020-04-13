<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\MissionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, ['label' => 'Nom'])
            ->add('userSkillsRequirement', CollectionType::class, [
                'entry_type' => MissionTypeUserSkillsType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => 'Bénévoles',
            ])
            ->add('assetTypesRequirement', CollectionType::class, [
                'entry_type' => MissionTypeAssetTypesType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => 'Véhicules',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MissionType::class,
        ]);
    }
}
