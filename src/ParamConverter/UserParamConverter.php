<?php

declare(strict_types=1);

namespace App\ParamConverter;

use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserParamConverter implements ParamConverterInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();
        $userId = $request->attributes->getInt($name);
        $search = ['id' => $userId];

        $organizationId = $request->attributes->get('organization', null);
        if (null !== $organizationId) {
            $search += ['organization' => $organizationId];
        }

        $user = $this->userRepository->findOneBy($search);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('User with id "%d" and organization id "%d" does not exist.', $userId, $organizationId));
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
