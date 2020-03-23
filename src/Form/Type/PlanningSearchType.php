<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PlanningSearchType extends AbstractType
{
    private array $availableSkillSets;

    public function __construct(array $availableSkillSets)
    {
        $this->availableSkillSets = $availableSkillSets;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('from', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'label' => 'Visualiser les jours de ',
                'with_minutes' => false,
            ])
            ->add('to', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'label' => 'à',
                'with_minutes' => false,
            ])
            ->add('availableFrom', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'Rechercher les disponibilités de ',
                'with_minutes' => false,
            ])
            ->add('availableTo', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'à',
                'data' => (new DateTimeImmutable('today'))->add(new \DateInterval('P1D')),
                'with_minutes' => false,
            ])
            ->add('organizations', EntityType::class, [
                'label' => 'Structures',
                'class' => Organization::class,
                'multiple' => true,
                'choice_label' => 'name',
                'required' => false,
            ])
            ->add('volunteer', CheckboxType::class, [
                'label' => 'Bénévoles',
            ])
            ->add('volunteerSkills', ChoiceType::class, [
                'label' => 'Compétences',
                'choices' => array_flip($this->availableSkillSets),
                'multiple' => true,
                'required' => false,
            ])
            ->add('volunteerEquipped', CheckboxType::class, [
                'label' => 'Avec uniforme seulement',
            ])
            ->add('volunteerHideVulnerable', CheckboxType::class, [
                'label' => 'Cacher les personnes signalées comme vulnérables',
            ])
            ->add('asset', CheckboxType::class, [
                'label' => 'Véhicules',
            ])
            ->add('assetTypes', ChoiceType::class, [
                'label' => 'Type de véhicules',
                'choices' => array_flip(CommissionableAsset::TYPES),
                'multiple' => true,
                'required' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Filtrer'])
        ;

        // Cannot use contraint in upper types, because it's not bound to an entity (therefore PropertyAccessor cannot succeed)
        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $data = $event->getData() ?? [];
            if (array_key_exists('from', $data) && array_key_exists('to', $data) && $data['from'] >= $data['to']) {
                throw new \InvalidArgumentException('Invalid payload'); // TODO Put a better error
            }
        });
    }
}
