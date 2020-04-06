<?php

declare(strict_types=1);

namespace App\Controller\Organization\Planning;

use App\Domain\AvailabilitiesDomain;
use App\Domain\DatePeriodCalculator;
use App\Domain\PlanningDomain;
use App\Domain\SkillSetDomain;
use App\Entity\CommissionableAsset;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\CacheExtension\CacheStrategyInterface;

/**
 * @Route("/", name="planning", methods={"GET"})
 */
class PlanningController extends AbstractController
{
    private SkillSetDomain $skillSetDomain;
    private PlanningDomain $planningDomain;
    private CacheStrategyInterface $cacheStrategy;
    private CacheItemPoolInterface $cacheTwig;

    public function __construct(
        SkillSetDomain $skillSetDomain,
        PlanningDomain $planningDomain,
        CacheStrategyInterface $cacheStrategy,
        CacheItemPoolInterface $cacheTwig
    ) {
        $this->skillSetDomain = $skillSetDomain;
        $this->planningDomain = $planningDomain;
        $this->cacheStrategy = $cacheStrategy;
        $this->cacheTwig = $cacheTwig;
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->planningDomain->generateForm();
        $filters = $this->planningDomain->generateFilters($form);

        if (!isset($filters['from'], $filters['to'])) {
            // This may happen if the passed date is invalid. TODO check it before, the format must be 2020-03-30T00:00:00
            throw $this->createNotFoundException();
        }

        $periodCalculator = DatePeriodCalculator::createRoundedToDay(
            $filters['from'],
            new \DateInterval(AvailabilitiesDomain::SLOT_INTERVAL),
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

        return $this->render('organization/planning/planning.html.twig', [
            'filters' => $filters,
            'form' => $form->createView(),
            'periodCalculator' => $periodCalculator,
            'assetsTypes' => CommissionableAsset::TYPES,
            'usersSkills' => $this->skillSetDomain->getSkillSet(),
            'importantSkills' => $this->skillSetDomain->getImportantSkills(),
            'importantSkillsToDisplay' => $this->skillSetDomain->getSkillsToDisplay(),
        ]);
    }
}
