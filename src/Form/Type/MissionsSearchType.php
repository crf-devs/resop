<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\MissionType;
use App\Entity\Organization;
use App\Repository\MissionTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionsSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $builder->getData()['organization'] ?? null;
        if (!$organization instanceof Organization || null === $organization->id) {
            throw new \InvalidArgumentException('This type must be initialized with an already persisted organization');
        }

        $builder
            ->add('from', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'organization.mission.showFrom',
                'with_minutes' => false,
                'required' => false,
            ])
            ->add('to', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'calendar.to',
                'with_minutes' => false,
                'required' => false,
            ])
            ->add('missionTypes', EntityType::class, [
                'label' => 'organization.missionType.mainTitle',
                'class' => MissionType::class,
                'query_builder' => static function (MissionTypeRepository $repository) use ($organization) {
                    return $repository->findByOrganizationQb($organization);
                },
                'multiple' => true,
                'choice_label' => 'name',
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
