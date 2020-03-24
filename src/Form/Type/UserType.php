<?php

declare(strict_types=1);

namespace App\Form\Type;

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
    private array $availableSkillSets;

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

    public function __construct(array $availableSkillSets = [])
    {
        $this->availableSkillSets = $availableSkillSets;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $occupationChoices = (array) array_combine(self::DEFAULT_OCCUPATIONS, self::DEFAULT_OCCUPATIONS);
        $occupationChoices += ['Autre :' => '-'];
        $builder
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
                'required' => false,
                'expanded' => true,
                'placeholder' => false,
            ])
            ->add('organizationOccupation', TextType::class, [
                'required' => false,
            ])
            ->add('vulnerable', ChoiceType::class, [
                'choices' => [
                    'Je fais partie des personnes vulnérables' => 1,
                    'Je ne fais PAS partie des personnes vulnérables' => 0,
                ],
                'expanded' => true,
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
            ])
            ->add('skillSet', ChoiceType::class, [
                'choices' => array_flip($this->availableSkillSets),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('submit', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var User $user */
            $user = $event->getData();
            $form = $event->getForm();

            if (null === $user->getId()) {
                $form->add('identificationNumber', TextType::class);
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
