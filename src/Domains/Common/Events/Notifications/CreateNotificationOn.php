<?php

namespace Domains\Common\Events\Notifications;

use Domains\Common\Models\Account\UserCompanyAccountContract;
use Domains\Common\Models\Account\UserContract;

interface CreateNotificationOn
{
    /**
     * User company account to link the notification with
     * @return UserCompanyAccountContract
     */
    public function target(): UserCompanyAccountContract;

    /**
     * User that created the notification
     * Use SystemUserBuilder::user() in case of system actions
     *
     * @return UserContract
     */
    public function createdBy(): UserContract;
}
