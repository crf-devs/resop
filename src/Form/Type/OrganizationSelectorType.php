<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class OrganizationSelectorType extends AbstractType
{
    private OrganizationRepository $organizationRepository;
    private UrlGeneratorInterface $router;

    public function __construct(OrganizationRepository $organizationRepository, UrlGeneratorInterface $router)
    {
        $this->organizationRepository = $organizationRepository;
        $this->router = $router;
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
                    'label' => 'organization.children_selector.label',
                    'query_builder' => $this->organizationRepository->findChildrenQueryBuilder($options['currentOrganization']),
                    'choice_attr' => function (Organization $choice) use ($options) {
                        return [
                            'data-url' => $this->router->generate($options['route_to_redirect'], ['id' => $choice->getId()]),
                        ];
                    },
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['currentOrganization', 'route_to_redirect']);
    }
}
