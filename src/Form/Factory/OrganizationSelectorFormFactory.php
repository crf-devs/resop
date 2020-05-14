<?php

declare(strict_types=1);

namespace App\Form\Factory;

use App\Entity\Organization;
use App\Form\Type\OrganizationSelectorType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class OrganizationSelectorFormFactory
{
    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function createForm(Organization $organization, Organization $loggedOrganization): FormInterface
    {
        return $this->formFactory->create(
            OrganizationSelectorType::class,
            ['organization' => $organization],
            ['currentOrganization' => $loggedOrganization]
        );
    }
}
