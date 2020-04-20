<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Organization;
use App\Repository\AssetTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class MissionTypeAssetTypesType extends AbstractType
{
    protected array $assetTypes;

    public function __construct(Security $security, AssetTypeRepository $assetTypeRepository)
    {
        /** @var Organization $organization */
        $organization = $security->getUser();

        $assetTypes = $assetTypeRepository->findByOrganization($organization->getParentOrganization());
        foreach ($assetTypes as $assetType) {
            $this->assetTypes[$assetType->id] = $assetType->name;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'organization.missionType.assetTypes.type',
                'choices' => array_flip($this->assetTypes),
                'placeholder' => '',
                'row_attr' => ['class' => 'row'],
                'label_attr' => ['class' => 'col-sm-2'],
                'attr' => ['class' => 'col-sm-6'],
            ])
            ->add('number', IntegerType::class, [
                'label' => 'organization.missionType.assetTypes.number',
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
