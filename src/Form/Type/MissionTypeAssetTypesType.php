<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Repository\AssetTypeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionTypeAssetTypesType extends AbstractType
{
    private RequestStack $requestStack;
    private AssetTypeRepository $assetTypeRepository;

    public function __construct(RequestStack $requestStack, AssetTypeRepository $assetTypeRepository)
    {
        $this->requestStack = $requestStack;
        $this->assetTypeRepository = $assetTypeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // As the result is stored in JSON, we don't use EntityType here because we only want the entity.id and not the full serialized object
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'organization.missionType.assetTypes.type',
                'choices' => array_flip($this->getAssetTypes()),
                'placeholder' => '',
                'row_attr' => ['class' => 'row'],
                'label_attr' => ['class' => 'col-sm-2'],
                'attr' => ['class' => 'col-sm-6'],
            ])
            ->add('number', IntegerType::class, [
                'label' => 'organization.missionType.assetTypes.number',
                'row_attr' => ['class' => 'row'],
                'label_attr' => ['class' => 'col-sm-2'],
                'attr' => ['class' => 'col-sm-4'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'row_attr' => [
                'class' => 'shadow p-3 mt-3 rounded col-sm-10 mx-auto',
            ],
        ]);
    }

    private function getAssetTypes(): array
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        $organization = $request->attributes->get('organization');
        $assetTypes = $this->assetTypeRepository->findByOrganization($organization->getParentOrganization());
        $data = [];
        foreach ($assetTypes as $assetType) {
            $data[$assetType->id] = $assetType->name;
        }

        return $data;
    }
}
