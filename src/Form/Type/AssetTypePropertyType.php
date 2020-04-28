<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\AssetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetTypePropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('key', TextType::class, [
                'required' => true,
                'label' => 'organization.asset_type.properties.key',
                'attr' => ['class' => 'disable-on-edit key-input'],
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'label' => 'organization.asset_type.properties.format',
                'choices' => array_flip([
                    AssetType::TYPE_NUMBER => 'organization.asset_type.properties.formats.number',
                    AssetType::TYPE_BOOLEAN => 'organization.asset_type.properties.formats.boolean',
                    AssetType::TYPE_SMALL_TEXT => 'organization.asset_type.properties.formats.small_text',
                    AssetType::TYPE_TEXT => 'organization.asset_type.properties.formats.text',
                ]),
                'placeholder' => '',
                'attr' => ['class' => 'disable-on-edit'],
            ])
            ->add('label', TextType::class, [
                'label' => 'organization.asset_type.properties.label',
                'required' => true,
            ])
            ->add('help', TextType::class, [
                'label' => 'organization.asset_type.properties.help',
                'required' => false,
            ])
            ->add('required', ChoiceType::class, [
                'label' => 'organization.asset_type.properties.required',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => ['common.no' => false, 'common.yes' => true],
            ])
            ->add('hidden', ChoiceType::class, [
                'label' => 'organization.asset_type.properties.hidden',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices' => ['common.no' => false, 'common.yes' => true], // Order matters
                'attr' => ['class' => 'hide-on-create'],
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
