<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\AssetType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetPropertiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['config'] as $property) {
            if (true === $property['hidden']) {
                continue;
            }

            $options = [
                'label' => $property['label'],
                'help' => $property['help'] ?: '',
                'required' => $property['required'],
            ];

            switch ($property['type']) {
                case AssetType::TYPE_SMALL_TEXT:
                    $formClass = TextType::class;
                    break;
                case AssetType::TYPE_TEXT:
                    $formClass = TextareaType::class;
                    break;
                case AssetType::TYPE_NUMBER:
                    $formClass = IntegerType::class;
                    break;
                case AssetType::TYPE_BOOLEAN:
                    $formClass = ChoiceType::class;
                    $options['expanded'] = true;
                    $options['choices'] = ['oui' => 'oui', 'non' => 'non'];
                    break;
                default:
                    throw new \InvalidArgumentException('Unsupported property.type');
            }

            $builder->add($property['key'], $formClass, $options);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['config' => []]);
    }
}
