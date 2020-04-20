<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\MissionType;
use App\Entity\Organization;
use App\Repository\MissionTypeRepository;
use App\Repository\OrganizationRepository;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningForecastType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $builder->getData()['organization'];
        if (!$organization instanceof Organization || null === $organization->id) {
            throw new \InvalidArgumentException('PlanningForecastType must be initialized with an already persisted organization');
        }

        $builder
            ->add('availableFrom', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'Rechercher les ressources disponibles de ',
                'with_minutes' => false,
                'required' => true,
            ])
            ->add('availableTo', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'à',
                'data' => (new DateTimeImmutable('today'))->add(new \DateInterval('P1D')),
                'with_minutes' => false,
                'required' => true,
            ])
            ->add('organizations', EntityType::class, [
                'label' => 'Structures',
                'class' => Organization::class,
                'group_by' => 'parentName',
                'query_builder' => static function (OrganizationRepository $repository) use ($organization) {
                    return $repository->findByParentQueryBuilder($organization);
                },
                'multiple' => true,
                'choice_label' => 'name',
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-actions-box' => 'true',
                ],
            ])
            ->add('missionTypes', EntityType::class, [
                'label' => 'Types de missions',
                'class' => MissionType::class,
                'query_builder' => static function (MissionTypeRepository $repository) use ($organization) {
                    return $repository->findByOrganizationQb($organization);
                },
                'multiple' => true,
                'choice_label' => 'name',
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-actions-box' => 'true',
                ],
            ])
            ->add('onlyFullyEquiped', CheckboxType::class, [
                'label' => 'Avec uniforme seulement',
                'required' => false,
            ])
            ->add('displayAvailableWithBooked', CheckboxType::class, [
                'label' => 'Compter aussi les ressources déjà engagées',
                'required' => false,
            ])
            ->add('displayVulnerables', CheckboxType::class, [
                'label' => 'Compter aussi les personnes signalées comme vulnérables',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
