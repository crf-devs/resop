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

final class UserType extends AbstractType
{
    private const DEFAULT_OCCUPATIONS = [
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

    private SkillSetDomain $skillSetDomain;

    public function __construct(SkillSetDomain $skillSetDomain)
    {
        $this->skillSetDomain = $skillSetDomain;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $occupationChoices = (array) array_combine(self::DEFAULT_OCCUPATIONS, self::DEFAULT_OCCUPATIONS);
        $occupationChoices += ['Autre :' => '-'];
        $builder
            ->add('organization', OrganizationEntityType::class, [
                'label' => 'Votre structure de rattachement',
                'help' => 'À quelle unité locale êtes-vous rattaché.e ?',
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Numéro de téléphone portable',
            ])
            ->add('emailAddress', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])
            ->add('birthday', BirthdayType::class, [
                'format' => 'dd-MMMM-yyyy',
                'input' => 'string',
                'label' => 'Date de naissance',
            ])
            ->add('occupation', ChoiceWithOtherType::class, [
                'choices' => $occupationChoices,
                'expanded' => true,
                'placeholder' => false,
                'required' => false,
                'label' => 'Quelle est votre profession ?',
                'attr' => ['class' => 'js-occupation'],
            ])
            ->add('organizationOccupation', TextType::class, [
                'required' => false,
                'label' => 'Fonction de cadre au sein de votre structure d\'emploi',
            ])
            ->add('vulnerable', ChoiceType::class, [
                'choices' => [
                    'Je ne fais PAS partie des personnes vulnérables' => 0,
                    'Je fais partie des personnes vulnérables' => 1,
                ],
                'data' => 1,
                'expanded' => true,
                'help' => '<ul><li>malade chronique</li><li>obésité morbide</li><li>syndrome grippal</li><li>immunodéprimé</li><li>personne mineure ou personne de plus de 70 ans</li><li>avis défavorable de votre unité locale ou du pole santé (local ou territorial)</li></ul>',
                'help_html' => true,
            ])
            ->add('fullyEquipped', ChoiceType::class, [
                'choices' => [
                    'Oui' => 1,
                    'Non' => 0,
                ],
                'required' => true,
                'expanded' => true,
                'placeholder' => false,
                'label' => 'Avez-vous un uniforme en dotation chez vous ?',
            ])
            ->add('skillSet', ChoiceType::class, [
                'choices' => array_flip($this->skillSetDomain->getSkillSet()),
                'multiple' => true,
                'expanded' => true,
                'label' => 'Quelles sont vos compétences Croix-Rouge ?',
                'help' => 'Cochez toutes vos compétences',
            ])
            ->add('submit', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var User $user */
            $user = $event->getData();
            $form = $event->getForm();

            if (null === $user->getId()) {
                $form->add('identificationNumber', TextType::class, [
                    'label' => 'NIVOL',
                ]);
            } else {
                $form->remove('birthday');
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
