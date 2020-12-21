<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class CurrentPasswordValidator extends ConstraintValidator
{
    private $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * @param User                       $user
     * @param Constraint|CurrentPassword $constraint
     */
    public function validate($user, Constraint $constraint): void
    {
        if (!$constraint instanceof CurrentPassword) {
            throw new UnexpectedTypeException($constraint, CurrentPassword::class);
        }

        if (!empty($user->getPassword()) && !empty($user->plainPassword) && (null === $user->currentPassword || !$this->userPasswordEncoder->isPasswordValid($user, $user->currentPassword))) {
            $this->context->buildViolation($constraint->getMessage())
                ->atPath('currentPassword')
                ->addViolation();
        }
    }
}
