<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\Mission;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Contracts\Translation\TranslatorInterface;

class MissionDomain
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getCsvHeaders(): array
    {
        return [
            $this->translator->trans('common.type'),
            $this->translator->trans('organization.mission.title'),
            $this->translator->trans('common.start'),
            $this->translator->trans('common.end'),
            $this->translator->trans('organization.mission.itemFull'),
            $this->translator->trans('organization.label'),
            $this->translator->trans('common.name'),
            $this->translator->trans('user.skills'),
            $this->translator->trans('user.identificationNumber'),
            $this->translator->trans('user.email'),
            $this->translator->trans('common.phoneNumberShort'),
            $this->translator->trans('user.dob'),
        ];
    }

    public function toCsvArray(Mission $mission): array
    {
        $missionArray = [
            $mission->type ? $mission->type->name : '',
            $mission->name,
            $mission->startTime ? $mission->startTime->format('Y-m-d H:i') : '',
            $mission->endTime ? $mission->endTime->format('Y-m-d H:i') : '',
        ];

        if (!\count($mission->users) && !\count($mission->assets)) {
            return [$missionArray];
        }

        $res = [];
        $phoneUtil = PhoneNumberUtil::getInstance();
        foreach ($mission->users as $user) {
            $res[] = array_merge($missionArray, [
                $this->translator->trans('organization.userTitle'),
                $user->getNotNullOrganization()->getName(),
                $user->getFullName(),
                implode(' ', $user->skillSet),
                $user->getIdentificationNumber(),
                $user->emailAddress,
                $user->phoneNumber ? $phoneUtil->format($user->phoneNumber, PhoneNumberFormat::E164) : '',
                $user->birthday,
            ]);
        }

        foreach ($mission->assets as $asset) {
            $res[] = array_merge($missionArray, [
                $this->translator->trans('organization.assetTitle'),
                $asset->organization->getName(),
                (string) $asset->name,
            ]);
        }

        return $res;
    }
}
