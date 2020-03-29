<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $birthdayOptions = [
            'format' => 'dd MMMM yyyy',
            'input' => 'string',
        ];
        if (false === $builder->getForm()->isSubmitted()) {
            $birthdayOptions['data'] = '1990-01-01';
        }

        $builder
            ->add('identifier', TextType::class)
            ->add('birthday', BirthdayType::class, $birthdayOptions);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'authenticate',
        ]);
    }
}
