<?php

declare(strict_types=1);

namespace App\Controller\Organization\Planning;

use App\Domain\DatePeriodCalculator;
use App\Domain\PlanningDomain;
use App\Domain\SkillSetDomain;
use App\Entity\Organization;
use App\Repository\AssetTypeRepository;
use Psr\Cache\CacheItemPoolInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\CacheExtension\CacheStrategyInterface;

/**
 * @Route(name="app_organization_planning", methods={"GET"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', organization)")
 */
class PlanningController extends AbstractController
{
    private AssetTypeRepository $assetTypeRepository;
    private SkillSetDomain $skillSetDomain;
    private PlanningDomain $planningDomain;
    private CacheStrategyInterface $cacheStrategy;
    private CacheItemPoolInterface $cacheTwig;

    public function __construct(
        AssetTypeRepository $assetTypeRepository,
        SkillSetDomain $skillSetDomain,
        PlanningDomain $planningDomain,
        CacheStrategyInterface $cacheStrategy,
        CacheItemPoolInterface $cacheTwig
    ) {
        $this->assetTypeRepository = $assetTypeRepository;
        $this->skillSetDomain = $skillSetDomain;
        $this->planningDomain = $planningDomain;
        $this->cacheStrategy = $cacheStrategy;
        $this->cacheTwig = $cacheTwig;
    }

    public function __invoke(Request $request, Organization $organization, string $slotInterval): Response
    {
        $form = $this->planningDomain->generateForm($organization);
        $filters = $this->planningDomain->generateFilters($form, $organization);

        if (!isset($filters['from'], $filters['to'])) {
            // This may happen if the passed date is invalid. TODO check it before, the format must be 2020-03-30T00:00:00
            throw $this->createNotFoundException();
        }

        $periodCalculator = DatePeriodCalculator::createRoundedToDay(
            $filters['from'],
            \DateInterval::createFromDateString($slotInterval),
            $filters['to']
        );

        $lastUpdate = $this->planningDomain->generateLastUpdateAndCount($filters)['lastUpdate'];
        $cacheKey = $this->cacheStrategy->generateKey('organization_planning', $filters);
        /** @var CacheItem $item */
        $item = $this->cacheTwig->getItem($cacheKey);
        if ($item->isHit()
            && isset($item->getMetadata()[CacheItem::METADATA_CTIME])
            && $item->getMetadata()[CacheItem::METADATA_CTIME] < ceil($lastUpdate / 100)
        ) {
            // New availabilities in planning: invalidate planning cache
            $this->cacheTwig->deleteItem($cacheKey);
        }

        $assetTypes = $this->assetTypeRepository->findByOrganization($organization->getParentOrganization());

        return $this->render('organization/planning/planning.html.twig', [
            'filters' => $filters,
            'organization' => $organization,
            'form' => $form->createView(),
            'periodCalculator' => $periodCalculator,
            'assetsTypes' => $assetTypes,
            'usersSkills' => $this->skillSetDomain->getSkillSet(),
            'importantSkillsToDisplay' => $this->skillSetDomain->getSkillsToDisplay(),
        ]);
    }
}
