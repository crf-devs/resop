<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrganizationSelectorType extends AbstractType
{
    private OrganizationRepository $organizationRepository;

    public function __construct(OrganizationRepository $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * @param array{currentOrganization: Organization, route_to_redirect: string} $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!$options['currentOrganization']->isParent()) {
            return;
        }

        $builder
            ->add(
                'organization',
                EntityType::class,
                [
                    'class' => Organization::class,
                    'label' => 'organization.childrenSelector.label',
                    'query_builder' => $this->organizationRepository->findByParentQueryBuilder($options['currentOrganization']),
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['currentOrganization']);
    }
}
