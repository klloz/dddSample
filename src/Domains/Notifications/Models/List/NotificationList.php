<?php

namespace Domains\Notifications\Models\List;

use Domains\Common\Models\Account\UserCompanyAccountContract;
use Domains\Common\Models\Paginator;
use Domains\Notifications\Repositories\NotificationRepositoryContract;

class NotificationList
{
    private Paginator $paginator;

    /**
     * @param Paginator $paginator
     */
    private function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @param NotificationRepositoryContract $notificationRepository
     * @param UserCompanyAccountContract $userCompanyAccount
     * @param NotificationListFilters $filters
     * @return static
     */
    public static function userCompanyAccount(
        NotificationRepositoryContract $notificationRepository,
        UserCompanyAccountContract $userCompanyAccount,
        NotificationListFilters $filters,
    ): self {
        $paginator = $notificationRepository->findByUserCompanyAccount($userCompanyAccount, $filters);

        return new self($paginator);
    }

    /**
     * @return Paginator
     */
    public function paginator(): Paginator
    {
        return $this->paginator;
    }
}
