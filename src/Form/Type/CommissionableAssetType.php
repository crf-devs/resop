<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\AssetType;
use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CommissionableAssetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Organization $organization */
        $organization = $builder->getForm()->getData()->organization;
        /** @var AssetType $assetType */
        $assetType = $builder->getForm()->getData()->assetType;

        $builder
            ->add('name', TextType::class, [
                'label' => 'asset.name',
            ])
            ->add('properties', DynamicPropertiesType::class, [
                'label' => false,
                'config' => $assetType->properties,
            ])
            ->add('submit', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($organization): void {
            $form = $event->getForm();
            if ($organization->isParent()) {
                $form->add('organization', OrganizationEntityType::class, [
                    'placeholder' => '',
                    'label' => 'organization.label',
                    'query_builder' => function (OrganizationRepository $repository) use ($organization) {
                        return $repository->findByIdOrParentIdQueryBuilder($organization->getId());
                    },
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommissionableAsset::class,
        ]);
    }
}
