<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class CurrentPassword extends Constraint
{
    public function getMessage(): string
    {
        return 'Cette valeur n\'est pas valide.';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
