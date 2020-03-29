<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrganizationEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Organization::class,
            'choice_label' => 'name',
            'query_builder' => static function (OrganizationRepository $repository): QueryBuilder {
                return $repository->createActiveOrganizationQueryBuilder();
            },
        ]);
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
