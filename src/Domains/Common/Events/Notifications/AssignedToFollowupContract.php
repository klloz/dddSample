<?php

namespace Domains\Common\Events\Notifications;

use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\Calendar\EventContract;
use Domains\Common\Models\Permission\ActionPerformedByContract;

interface AssignedToFollowupContract extends CreateNotificationOn
{
    /**
     * @return ActionPerformedByContract
     */
    public function performedBy(): ActionPerformedByContract;

    /**
     * @return EventContract
     */
    public function event(): EventContract;

    /**
     * @return UserContract
     */
    public function user(): UserContract;
}
