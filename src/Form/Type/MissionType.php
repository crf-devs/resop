<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\CommissionableAsset;
use App\Entity\Mission;
use App\Entity\MissionType as MissionTypeEntity;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\CommissionableAssetRepository;
use App\Repository\MissionTypeRepository;
use App\Repository\UserRepository;
use App\Twig\Extension\UserExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionType extends AbstractType
{
    private UserExtension $userExtension;

    public function __construct(UserExtension $userExtension)
    {
        $this->userExtension = $userExtension;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $builder->getData()->organization ?? null;
        if (!$organization instanceof Organization || null === $organization->id) {
            throw new \InvalidArgumentException('Mission form must be initialized with an already persisted organization');
        }

        $builder
            ->add('name', null, ['label' => 'organization.mission.name'])
            ->add('type', EntityType::class, [
                'label' => 'common.type',
                'class' => MissionTypeEntity::class,
                'query_builder' => static function (MissionTypeRepository $repository) use ($organization) {
                    return $repository->findByOrganizationQb($organization);
                },
                'choice_label' => 'name',
                'required' => false,
            ])
            ->add('startTime', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'common.start',
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'organization.mission.comment',
                'required' => false,
                'empty_data' => '',
            ])
            ->add('endTime', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'common.end',
                'required' => false,
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'label' => 'organization.users',
                'query_builder' => static function (UserRepository $repository) use ($organization) {
                    return $repository->findByOrganizationAndChildrenQb($organization->getParentOrganization(), true);
                },
                'choice_label' => fn (User $user) => (string) $user,
                'multiple' => true,
                'required' => false,
                'choice_attr' => function (User $user) {
                    return [
                        'data-content' => $user.' '.$this->userExtension->userBadges($user),
                    ];
                },
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true',
                ],
            ])
            ->add('assets', EntityType::class, [
                'class' => CommissionableAsset::class,
                'label' => 'organization.assets',
                'query_builder' => static function (CommissionableAssetRepository $repository) use ($organization) {
                    return $repository->findByOrganizationAndChildrenQb($organization->getParentOrganization(), true);
                },
                'choice_label' => fn (CommissionableAsset $asset) => "{$asset->organization->name} / ".$asset,
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }
}
