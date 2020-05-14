<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\AssetType;
use App\Entity\Organization;
use App\Repository\AssetTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreAddAssetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $parentOrganization = $options['organization']->getParentOrganization();

        $builder
            ->add('type', EntityType::class, [
                'required' => true,
                'class' => AssetType::class,
                'choice_name' => 'name',
                'query_builder' => fn (AssetTypeRepository $assetTypeRepository) => $assetTypeRepository->findByOrganizationQB($parentOrganization),
            ])
            ->add('submit', SubmitType::class, ['label' => 'Continuer'])
            ->add('organizationId', HiddenType::class)
        ;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['organization'])
            ->addAllowedTypes('organization', Organization::class)
            ->setDefaults([
                'csrf_protection' => false,
            ]);
    }
}
