<?php

namespace Infrastructure\Persistence\Doctrine\Repositories\Notifications;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Domains\Common\Models\Account\UserCompanyAccountContract;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Paginator;
use Domains\Notifications\Models\List\NotificationListFilters;
use Domains\Notifications\Models\Notification;
use Domains\Notifications\Repositories\NotificationRepositoryContract;

class DoctrineNotificationRepository extends EntityRepository implements NotificationRepositoryContract
{
    /**
     * @inheritDoc
     */
    public function findById(AggregateRootId $id): ?Notification
    {
        $builder = $this->createQueryBuilder('Notification')
            ->where('Notification.uuid = :uuid')
            ->setParameter('uuid', (string)$id);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function store(Notification $model): NotificationRepositoryContract
    {
        $this->getEntityManager()->persist($model);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function remove(Notification $model): NotificationRepositoryContract
    {
        $this->getEntityManager()->remove($model);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flush(): NotificationRepositoryContract
    {
        $this->getEntityManager()->flush();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function findByUserCompanyAccount(
        UserCompanyAccountContract $userCompanyAccount,
        NotificationListFilters $filters
    ): Paginator {
        $baseQb = $this->createQueryBuilder('notification');
        $baseQb->where($baseQb->expr()->eq('notification.target', ':target'))
            ->andWhere($baseQb->expr()->gte('notification.createdAt', ':dateFrom'))
            ->orderBy('notification.' . $filters->sortBy(), $filters->sortDir())
            ->setParameter('target', $userCompanyAccount)
            ->setParameter('dateFrom', $filters->dateFrom()->format('Y-m-d H:i:s'));

        if ($filters->excludeDisplayed()) {
            $baseQb->andWhere($baseQb->expr()->isNull('notification.displayedAt'));
        }

        if ($filters->excludeRead()) {
            $baseQb->andWhere($baseQb->expr()->isNull('notification.readAt'));
        }

        $paginatedQb = (clone $baseQb)
            ->setFirstResult($filters->offset())
            ->setMaxResults($filters->perPage());

        return new Paginator(
            $paginatedQb->getQuery()->getResult(),
            count($baseQb->getQuery()->getResult()),
            $filters->page(),
            $filters->perPage()
        );
    }

    /**
     * @inheritDoc
     */
    public function countNonDisplayed(UserCompanyAccountContract $userCompanyAccount, \DateTimeImmutable $dateFrom): int
    {
        $qb = $this->createQueryBuilder('notification');
        $qb->select($qb->expr()->count('notification'))
            ->where($qb->expr()->eq('notification.target', ':target'))
            ->andWhere($qb->expr()->gte('notification.createdAt', ':dateFrom'))
            ->andWhere($qb->expr()->isNull('notification.displayedAt'))
            ->setParameter('target', $userCompanyAccount)
            ->setParameter('dateFrom', $dateFrom->format('Y-m-d H:i:s'));

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * @inheritDoc
     */
    public function findNonDisplayedByIds(UserCompanyAccountContract $userCompanyAccount, array $ids): array
    {
        $qb = $this->createQueryBuilder('notification');
        $qb->where($qb->expr()->eq('notification.target', ':target'))
            ->andWhere($qb->expr()->in('notification.uuid', ':ids'))
            ->andWhere($qb->expr()->isNull('notification.displayedAt'))
            ->setParameter('target', $userCompanyAccount)
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findNonDisplayedOlderThan(UserCompanyAccountContract $userCompanyAccount, \DateTimeImmutable $olderThan): array
    {
        $qb = $this->createQueryBuilder('notification');
        $qb->where($qb->expr()->eq('notification.target', ':target'))
            ->andWhere($qb->expr()->lte('notification.createdAt', ':olderThan'))
            ->andWhere($qb->expr()->isNull('notification.displayedAt'))
            ->setParameter('target', $userCompanyAccount)
            ->setParameter('olderThan', $olderThan);

        return $qb->getQuery()->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findUnreadByIds(UserCompanyAccountContract $userCompanyAccount, array $ids): array
    {
        $qb = $this->createQueryBuilder('notification');
        $qb->where($qb->expr()->eq('notification.target', ':target'))
            ->andWhere($qb->expr()->in('notification.uuid', ':ids'))
            ->andWhere($qb->expr()->isNull('notification.readAt'))
            ->setParameter('target', $userCompanyAccount)
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findUnreadOlderThan(UserCompanyAccountContract $userCompanyAccount, \DateTimeImmutable $olderThan): array
    {
        $qb = $this->createQueryBuilder('notification');
        $qb->where($qb->expr()->eq('notification.target', ':target'))
            ->andWhere($qb->expr()->lte('notification.createdAt', ':olderThan'))
            ->andWhere($qb->expr()->isNull('notification.readAt'))
            ->setParameter('target', $userCompanyAccount)
            ->setParameter('olderThan', $olderThan);

        return $qb->getQuery()->getResult();
    }
}
