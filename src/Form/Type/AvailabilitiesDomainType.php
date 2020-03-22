<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Domain\AvailabilitiesDomain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AvailabilitiesDomainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('availabilityDomains', CollectionType::class, [
                'entry_type' => AvailabilityDomainType::class,
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => AvailabilitiesDomain::class,
            ])
        ;
    }
}
