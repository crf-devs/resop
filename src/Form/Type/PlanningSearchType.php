<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Domain\SkillSetDomain;
use App\Entity\AssetType;
use App\Entity\Organization;
use App\Repository\AssetTypeRepository;
use App\Repository\OrganizationRepository;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningSearchType extends AbstractType
{
    private SkillSetDomain $skillSetDomain;

    public function __construct(SkillSetDomain $skillSetDomain)
    {
        $this->skillSetDomain = $skillSetDomain;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organization = $builder->getData()['organization'];
        if (!$organization instanceof Organization || null === $organization->id) {
            throw new \InvalidArgumentException('PlanningSearchType must be initialized with an already persisted organization');
        }

        $builder
            ->add('from', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'organization.planning.showDaysFrom',
                'with_minutes' => false,
            ])
            ->add('to', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'calendar.to',
                'with_minutes' => false,
            ])
            ->add('availableFrom', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'organization.planning.showAvailabilitesFrom',
                'with_minutes' => false,
                'required' => false,
            ])
            ->add('availableTo', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'calendar.to',
                'data' => (new DateTimeImmutable('today'))->add(new \DateInterval('P1D')),
                'with_minutes' => false,
                'required' => false,
            ])
            ->add('displayAvailableWithBooked', CheckboxType::class, [
                'label' => 'Afficher aussi les ressources déjà engagées',
                'required' => false,
            ])
            ->add('minimumAvailableHours', IntegerType::class, [
                'label' => 'Afficher si disponible au moins',
                'required' => false,
                'attr' => [
                    'placeholder' => 'heures',
                    'min' => 0,
                ],
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
            ->add('hideUsers', CheckboxType::class, [
                'label' => 'organization.planning.hideUsers',
                'required' => false,
            ])
            ->add('userSkills', ChoiceType::class, [
                'label' => 'user.skills',
                'choices' => array_flip($this->skillSetDomain->getSkillSet()),
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-actions-box' => 'true',
                ],
            ])
            ->add('onlyFullyEquiped', CheckboxType::class, [
                'label' => 'organization.planning.uniformOnly',
                'required' => false,
            ])
            ->add('displayVulnerables', CheckboxType::class, [
                'label' => 'organization.planning.showVulnerableUsers',
                'required' => false,
            ])
            ->add('hideAssets', CheckboxType::class, [
                'label' => 'organization.planning.hideAssets',
                'required' => false,
            ])
            ->add('assetTypes', EntityType::class, [
                'class' => AssetType::class,
                'query_builder' => function (AssetTypeRepository $assetTypeRepository) use ($organization) {
                    return $assetTypeRepository->findByOrganizationQB($organization->isParent() ? $organization : $organization->parent);
                },
                'label' => 'common.type',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-actions-box' => 'true',
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'setDefaultFromTo']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'setViewDataFromTo']);
    }

    public static function setDefaultFromTo(FormEvent $event): void
    {
        $data = $event->getData();

        if (empty($data['from'])) {
            $data['from'] = new \DateTimeImmutable('today');
        }

        if (empty($data['to'])) {
            $data['to'] = (clone $data['from'])->add(new \DateInterval('P2D'));
        }

        $event->setData($data);
    }

    public static function setViewDataFromTo(FormEvent $event): void
    {
        $data = $event->getData();
        $formData = $event->getForm()->getData();

        if (empty($data['from'])) {
            $data['from'] = $formData['from']->format(\DateTimeInterface::W3C);
        }

        if (empty($data['to'])) {
            $data['to'] = $formData['to']->format(\DateTimeInterface::W3C);
        }

        $event->setData($data);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
