<?php

namespace Domains\Notifications\Repositories;

use Domains\Common\Models\Account\UserCompanyAccountContract;
use Domains\Common\Models\Paginator;
use Domains\Notifications\Models\List\NotificationListFilters;
use Domains\Notifications\Models\Notification;
use Domains\Notifications\Models\NotificationId;

interface NotificationRepositoryContract
{
    /**
     * @param NotificationId $id
     * @return Notification|null
     */
    public function findById(NotificationId $id): ?Notification;

    /**
     * Create or update notification
     * @param Notification $model
     * @return $this
     */
    public function store(Notification $model): self;

    /**
     * Remove given notification from data storage
     * @param Notification $model
     * @return $this
     */
    public function remove(Notification $model): self;

    /**
     * Flush changes to data storage
     * @return $this
     */
    public function flush(): self;

    /**
     * @param UserCompanyAccountContract $userCompanyAccount
     * @param NotificationListFilters $filters
     * @return Paginator
     */
    public function findByUserCompanyAccount(
        UserCompanyAccountContract $userCompanyAccount,
        NotificationListFilters $filters
    ): Paginator;

    /**
     * @param UserCompanyAccountContract $userCompanyAccount
     * @param \DateTimeImmutable $dateFrom
     * @return int
     */
    public function countNonDisplayed(UserCompanyAccountContract $userCompanyAccount, \DateTimeImmutable $dateFrom): int;

    /**
     * @param UserCompanyAccountContract $userCompanyAccount
     * @param string[] $ids
     * @return Notification[]
     */
    public function findNonDisplayedByIds(UserCompanyAccountContract $userCompanyAccount, array $ids): array;

    /**
     * @param UserCompanyAccountContract $userCompanyAccount
     * @param \DateTimeImmutable $olderThan
     * @return array
     */
    public function findNonDisplayedOlderThan(UserCompanyAccountContract $userCompanyAccount, \DateTimeImmutable $olderThan): array;

    /**
     * @param UserCompanyAccountContract $userCompanyAccount
     * @param array $ids
     * @return array
     */
    public function findUnreadByIds(UserCompanyAccountContract $userCompanyAccount, array $ids): array;

    /**
     * @param UserCompanyAccountContract $userCompanyAccount
     * @param \DateTimeImmutable $olderThan
     * @return array
     */
    public function findUnreadOlderThan(UserCompanyAccountContract $userCompanyAccount, \DateTimeImmutable $olderThan): array;
}
