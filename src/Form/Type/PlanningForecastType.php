<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\MissionType;
use App\Entity\Organization;
use App\Repository\MissionTypeRepository;
use App\Repository\OrganizationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningForecastType extends AbstractType
{
    private array $userProperties;

    public function __construct(array $userProperties)
    {
        $this->userProperties = $userProperties;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $builder->getData()['organization'] ?? null;
        if (!$organization instanceof Organization || null === $organization->id) {
            throw new \InvalidArgumentException('This type must be initialized with an already persisted organization');
        }

        $builder
            ->add('availableFrom', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'organization.planning.showAssetsFrom',
                'with_minutes' => false,
                'required' => true,
            ])
            ->add('availableTo', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'calendar.to',
                'with_minutes' => false,
                'required' => true,
            ])
            ->add('organizations', EntityType::class, [
                'label' => 'organization.pluralLabel',
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
                    'data-actions-box' => 'true',
                ],
            ])
            ->add('displayAvailableWithBooked', CheckboxType::class, [
                'label' => 'organization.planning.countAlreadyBooked',
                'required' => false,
            ])
            ->add('userPropertyFilters', PlanningDynamicFiltersType::class, [
                    'config' => array_filter(
                        $this->userProperties,
                        fn (array $userProperty) => DynamicPropertiesType::TYPE_BOOLEAN === $userProperty['type']
                    ),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
