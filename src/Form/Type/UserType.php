<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Domain\SkillSetDomain;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public const DISPLAY_NEW = 'new';
    public const DISPLAY_EDIT = 'edit';
    public const DISPLAY_ORGANIZATION = 'organization';

    protected const DEFAULT_OCCUPATIONS = [
        'user.occupation.pediatric',
        'user.occupation.nurse',
        'user.occupation.gp',
        'user.occupation.paramedic',
        'user.occupation.nursingAssistant',
        'user.occupation.nurseAnaesthetist',
        'user.occupation.midwife',
        'user.occupation.pharmacist',
        'user.occupation.otherHealth',
        'user.occupation.firefighter',
        'user.occupation.policeOfficer',
        'user.occupation.logistics',
    ];

    protected SkillSetDomain $skillSetDomain;

    public function __construct(SkillSetDomain $skillSetDomain)
    {
        $this->skillSetDomain = $skillSetDomain;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Organization|null $organization */
        $organization = $builder->getData()->organization;

        $occupationChoices = (array) array_combine(self::DEFAULT_OCCUPATIONS, self::DEFAULT_OCCUPATIONS);
        $occupationChoices += ['Autre' => '-'];
        $builder
            ->add('identificationNumber', TextType::class, [
                'empty_data' => '',
            ])
            ->add('organization', OrganizationEntityType::class, [
                'placeholder' => '',
                'query_builder' => static function (OrganizationRepository $repository) use ($organization) {
                    $qb = $repository
                        ->createQueryBuilder('o')
                        ->orderBy('o.parent', 'ASC')
                        ->addOrderBy('o.name', 'ASC');

                    if ($organization instanceof Organization) {
                        $qb = $repository->findByIdOrParentIdQueryBuilder($organization->getId(), $qb);
                    }

                    return $qb;
                },
            ])
            ->add('firstName', TextType::class, [
                'empty_data' => '',
            ])
            ->add('lastName', TextType::class, [
                'empty_data' => '',
            ])
            ->add('phoneNumber', TextType::class, [
                'empty_data' => '',
            ])
            ->add('emailAddress', EmailType::class, [
                'empty_data' => '',
            ])
            ->add('birthday', BirthdayType::class, [
                'format' => 'dd MMMM yyyy',
                'input' => 'string',
            ])
            ->add('occupation', ChoiceWithOtherType::class, [
                'choices' => $occupationChoices,
                'expanded' => true,
                'placeholder' => false,
                'required' => false,
                'attr' => ['class' => 'js-occupation'],
            ])
            ->add('organizationOccupation', TextType::class, [
                'required' => false,
            ])
            ->add('fullyEquipped', ChoiceType::class, [
                'choices' => [
                    'common.yes' => 1,
                    'common.no' => 0,
                ],
                'required' => true,
                'expanded' => true,
                'placeholder' => false,
            ])
            ->add('drivingLicence', ChoiceType::class, [
                'choices' => [
                    'common.yes' => 1,
                    'common.no' => 0,
                ],
                'required' => true,
                'expanded' => true,
                'placeholder' => false,
            ])
            ->add('skillSet', ChoiceType::class, [
                'choices' => array_flip($this->skillSetDomain->getSkillSet()),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('submit', SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options): void {
            $form = $event->getForm();
            if (self::DISPLAY_ORGANIZATION === $options['display_type']) {
                $form->add('vulnerable', ChoiceType::class, [
                    'choices' => [
                        'organization.user.isNotVulnerable' => 0,
                        'organization.user.isVulnerable' => 1,
                    ],
                    'expanded' => true,
                    'help' => 'user.detail.vulnerable.help',
                    'help_html' => true,
                ]);
            } else {
                $form->add('vulnerable', ChoiceType::class, [
                    'choices' => [
                        'user.detail.vulnerable.no' => 0,
                        'user.detail.vulnerable.yes' => 1,
                    ],
                    'expanded' => true,
                    'help' => 'user.detail.vulnerable.help',
                    'help_html' => true,
                ]);

                if (self::DISPLAY_EDIT === $options['display_type']) {
                    $form
                        ->remove('birthday')
                        ->remove('identificationNumber');
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('display_type')
            ->setRequired('display_type')
            ->addAllowedValues('display_type', [self::DISPLAY_NEW, self::DISPLAY_EDIT, self::DISPLAY_ORGANIZATION])
            ->setDefaults([
                'data_class' => User::class,
                'display_type' => self::DISPLAY_NEW,
            ]);
    }
}
