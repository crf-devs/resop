<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Domain\SkillSetDomain;
use App\Entity\User;
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
        'Compétences pédiatriques',
        'Infirmier.e',
        'Médecin',
        'Ambulancier.e',
        'Aide soignant.e',
        'Infirmier.e anesthésiste',
        'Sage femme',
        'Pharmacien',
        'Autre personnel de santé',
        'Pompier',
        'Gendarme / Policier',
        'Logisticien',
    ];

    protected SkillSetDomain $skillSetDomain;

    public function __construct(SkillSetDomain $skillSetDomain)
    {
        $this->skillSetDomain = $skillSetDomain;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $occupationChoices = (array) array_combine(self::DEFAULT_OCCUPATIONS, self::DEFAULT_OCCUPATIONS);
        $occupationChoices += ['Autre' => '-'];
        $builder
            ->add('identificationNumber', TextType::class)
            ->add('organization', OrganizationEntityType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('phoneNumber', TextType::class)
            ->add('emailAddress', EmailType::class)
            ->add('birthday', BirthdayType::class, [
                'format' => 'dd-MMMM-yyyy',
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
                    'Oui' => 1,
                    'Non' => 0,
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $vulnerableHelp = '<ul><li>malade chronique</li><li>obésité morbide</li><li>syndrome grippal</li><li>immunodéprimé</li><li>personne mineure ou personne de plus de 70 ans</li><li>avis défavorable de votre unité locale ou du pole santé (local ou territorial)</li></ul>';
            if (self::DISPLAY_ORGANIZATION === $options['display_type']) {
                $form->add('vulnerable', ChoiceType::class, [
                    'choices' => [
                        'fait PAS partie des personnes vulnérables' => 0,
                        'fait partie des personnes vulnérables' => 1,
                    ],
                    'expanded' => true,
                    'help' => $vulnerableHelp,
                    'help_html' => true,
                ]);
            } else {
                $form->add('vulnerable', ChoiceType::class, [
                    'choices' => [
                        'Je ne fais PAS partie des personnes vulnérables' => 0,
                        'Je fais partie des personnes vulnérables' => 1,
                    ],
                    'expanded' => true,
                    'help' => $vulnerableHelp,
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
