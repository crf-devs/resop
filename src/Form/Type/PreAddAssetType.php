<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\AssetType;
use App\Entity\Organization;
use App\Repository\AssetTypeRepository;
use LogicException;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class PreAddAssetType extends AbstractType
{
    protected AssetTypeRepository $assetTypeRepository;
    protected Organization $organization;

    public function __construct(Security $security, AssetTypeRepository $assetTypeRepository)
    {
        $this->assetTypeRepository = $assetTypeRepository;

        /** @var Organization $currentUser */
        $currentUser = $security->getUser();
        if (!$currentUser instanceof Organization) {
            throw new LogicException('Current user must be an organization');
        }

        $this->organization = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $this->organization->isParent() ? $this->organization : $this->organization->parent;

        $builder
            ->add('type', EntityType::class, [
                'required' => true,
                'class' => AssetType::class,
                'choice_name' => 'name',
                'query_builder' => fn (AssetTypeRepository $assetTypeRepository) => $assetTypeRepository->findByOrganizationQB($organization),
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
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
