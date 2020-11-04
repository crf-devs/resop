<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Domain\SkillSetDomain;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use libphonenumber\PhoneNumberFormat;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class UserType extends AbstractType
{
    public const DISPLAY_NEW = 'new';
    public const DISPLAY_EDIT = 'edit';
    public const DISPLAY_ORGANIZATION = 'organization';

    private SkillSetDomain $skillSetDomain;
    private array $userProperties;

    public function __construct(SkillSetDomain $skillSetDomain, array $userProperties)
    {
        $this->skillSetDomain = $skillSetDomain;
        $this->userProperties = $userProperties;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User|null $data */
        $data = $builder->getData();
        $organization = $data->organization ?? null;

        $builder
            ->add('organization', OrganizationEntityType::class, [
                'placeholder' => '',
                'query_builder' => static function (OrganizationRepository $repository) use ($organization) {
                    $qb = $repository
                        ->createQueryBuilder('o')
                        ->orderBy('o.parent', 'ASC')
                        ->addOrderBy('o.name', 'ASC');

                    if ($organization instanceof Organization) {
                        $qb = $repository->findByIdOrParentIdQueryBuilder($organization->getParentOrganization()->getId(), $qb);
                    }

                    return $qb;
                },
                'label' => self::DISPLAY_ORGANIZATION === $options['display_type'] ? 'organization.default' : 'user.detail.organization',
            ])
            ->add('firstName', TextType::class, [
                'empty_data' => '',
            ])
            ->add('lastName', TextType::class, [
                'empty_data' => '',
            ])
            ->add('phoneNumber', PhoneNumberType::class, [
                'default_region' => 'FR',
                'format' => PhoneNumberFormat::NATIONAL,
                'constraints' => [new NotNull()],
            ])
            ->add('emailAddress', EmailType::class, [
                'empty_data' => '',
            ])
            ->add('skillSet', ChoiceType::class, [
                'choices' => array_flip($this->skillSetDomain->getSkillSet()),
                'multiple' => true,
                'expanded' => true,
                'help' => self::DISPLAY_ORGANIZATION === $options['display_type'] ? null : 'user.detail.skillSet.help',
                'label' => self::DISPLAY_ORGANIZATION === $options['display_type'] ? 'organization.user.skillset' : 'user.detail.skillSet.label',
            ])
            ->add('properties', DynamicPropertiesType::class, [
                'label' => false,
                'config' => $this->userProperties,
            ]);

        if (self::DISPLAY_EDIT === $options['display_type']) {
            return;
        }

        $builder
            ->add('identificationNumber', TextType::class, [
                'empty_data' => '',
            ])
            ->add('birthday', BirthdayType::class, [
                'format' => 'dd MMMM yyyy',
                'input' => 'string',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('display_type')
            ->addAllowedValues('display_type', [self::DISPLAY_NEW, self::DISPLAY_EDIT, self::DISPLAY_ORGANIZATION])
            ->setDefaults([
                'data_class' => User::class,
                'display_type' => self::DISPLAY_NEW,
            ]);
    }
}
