<?php

declare(strict_types=1);

namespace App\ParamConverter;

use App\Entity\Organization;
use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserParamConverter implements ParamConverterInterface
{
    private UserRepository $userRepository;
    private OrganizationParamConverter $organizationParamConverter;

    public function __construct(UserRepository $userRepository, OrganizationParamConverter $organizationParamConverter)
    {
        $this->userRepository = $userRepository;
        $this->organizationParamConverter = $organizationParamConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();
        $id = $request->attributes->getInt($name);

        /** @var Organization|int|null $organization */
        $organization = $request->attributes->get('organization');
        if (null !== $organization) {
            // Force OrganizationParamConverter to retrieve the organization
            if (!\is_object($organization)) {
                $this->organizationParamConverter->apply($request, new ParamConverter(['name' => 'organization']));
                $organization = $request->attributes->get('organization');
            }

            $user = $this->userRepository->findOneByIdAndOrganization($id, $organization);
        } else {
            $user = $this->userRepository->find($id);
        }

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('User with id "%d" does not exist.', $id));
        }

        $request->attributes->set($name, $user);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return User::class === $configuration->getClass();
    }
}
