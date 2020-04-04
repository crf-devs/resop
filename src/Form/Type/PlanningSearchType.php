<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Domain\SkillSetDomain;
use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
                'label' => 'Visualiser les jours de ',
                'with_minutes' => false,
            ])
            ->add('to', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'à',
                'with_minutes' => false,
            ])
            ->add('availableFrom', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'Rechercher les disponibilités de ',
                'with_minutes' => false,
                'required' => false,
            ])
            ->add('availableTo', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'à',
                'data' => (new DateTimeImmutable('today'))->add(new \DateInterval('P1D')),
                'with_minutes' => false,
                'required' => false,
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
            ->add('hideUsers', CheckboxType::class, [
                'label' => 'Cacher les bénévoles',
                'required' => false,
            ])
            ->add('userSkills', ChoiceType::class, [
                'label' => 'Compétences',
                'choices' => array_flip($this->skillSetDomain->getSkillSet()),
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'selectpicker'],
            ])
            ->add('onlyFullyEquiped', CheckboxType::class, [
                'label' => 'Avec uniforme seulement',
                'required' => false,
            ])
            ->add('displayVulnerables', CheckboxType::class, [
                'label' => 'Afficher aussi les personnes signalées comme vulnérables',
                'required' => false,
            ])
            ->add('hideAssets', CheckboxType::class, [
                'label' => 'Cacher les véhicules',
                'required' => false,
            ])
            ->add('assetTypes', ChoiceType::class, [
                'label' => 'Type',
                'choices' => array_flip(CommissionableAsset::TYPES),
                'multiple' => true,
                'required' => false,
                'attr' => ['class' => 'selectpicker'],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();

            if (!isset($data['from'])) {
                $data['from'] = new \DateTimeImmutable('today');
            }

            if (!isset($data['to'])) {
                $data['to'] = (clone $data['from'])->add(new \DateInterval('P2D'));
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
