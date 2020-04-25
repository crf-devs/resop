<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\AssetType;
use App\Entity\Organization;
use App\Repository\AssetTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreAddAssetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $parentOrganization = $options['parent_organization'];
        if (!$parentOrganization instanceof Organization) {
            throw new \LogicException('parent_organization must be an instance of Organization');
        }

        $builder
            ->add('type', EntityType::class, [
                'required' => true,
                'class' => AssetType::class,
                'choice_name' => 'name',
                'query_builder' => fn (AssetTypeRepository $assetTypeRepository) => $assetTypeRepository->findByOrganizationQB($parentOrganization),
            ])
            ->add('submit', SubmitType::class, ['label' => 'Continuer'])
        ;
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['parent_organization']);
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
