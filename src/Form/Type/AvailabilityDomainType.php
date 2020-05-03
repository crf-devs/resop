<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Domain\AvailabilityDomain;
use App\Entity\AvailabilityInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AvailabilityDomainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
                /** @var AvailabilityDomain $data */
                $data = $event->getData();
                $form = $event->getForm();

                $attr = ['data-status' => AvailabilityInterface::STATUS_UNKNOW];

                if ($data->availability) {
                    $attr['data-status'] = $data->availability->getStatus();

                    if (!empty(trim($data->availability->getComment()))) {
                        $attr['data-comment'] = $data->availability->getComment();
                    }
                }

                $form->add('tick', CheckboxType::class, [
                    'label' => false,
                    'required' => false,
                    'disabled' => !$data->isEditable(),
                    'attr' => $attr,
                ]);
            });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => AvailabilityDomain::class,
            ]);
    }
}
