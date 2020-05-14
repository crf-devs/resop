<?php

declare(strict_types=1);

namespace App\ParamConverter;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrganizationParamConverter implements ParamConverterInterface
{
    private OrganizationRepository $organizationRepository;

    public function __construct(OrganizationRepository $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();
        $id = $request->attributes->getInt($name);

        if (!empty($organizationId = $request->query->getInt('organizationId'))
        ) {
            $id = $organizationId;
        }

        $organization = $this->organizationRepository->find($id);

        if (null === $organization) {
            throw new NotFoundHttpException(sprintf('Organization with id "%d" does not exist.', $id));
        }

        $request->attributes->set($name, $organization);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return Organization::class === $configuration->getClass();
    }
}
