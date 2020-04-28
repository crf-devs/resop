<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\AssetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, ['label' => 'Nom'])
            ->add('properties', CollectionType::class, [
                'entry_type' => AssetTypePropertyType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'label' => 'organization.asset_type.properties.main_title',
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'error_bubbling' => true,
            'data_class' => AssetType::class,
        ]);
    }
}
