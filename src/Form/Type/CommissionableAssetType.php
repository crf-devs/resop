<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CommissionableAssetType extends AbstractType
{
    private const TYPES = [
        'VL - Véhicule léger' => 'VL',
        'VPSP - Véhicule de premiers secours' => 'VPSP',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Organization $organization */
        $organization = $builder->getForm()->getData()->organization;

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => self::TYPES,
                'label' => 'Type',
            ])
            ->add('name', TextType::class, [
                'label' => 'Identifiant',
            ])
            ->add('hasMobileRadio', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Présence d\'un mobile radio ?',
            ])
            ->add('hasFirstAidKit', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Présence d\'un lot de secours ?',
            ])
            ->add('parkingLocation', TextType::class, [
                'required' => false,
                'label' => 'Lieu de stationnement',
            ])
            ->add('contact', TextType::class, [
                'required' => false,
                'label' => 'Qui contacter ?',
            ])
            ->add('seatingCapacity', IntegerType::class, [
                'label' => 'Combien de places ?',
            ])
            ->add('submit', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($organization): void {
            $form = $event->getForm();
            if ($organization->isParent()) {
                $form->add('organization', OrganizationEntityType::class, [
                    'placeholder' => '',
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
