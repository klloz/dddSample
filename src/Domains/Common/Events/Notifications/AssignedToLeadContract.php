<?php

namespace Domains\Common\Events\Notifications;

use Domains\Common\Models\Lead\LeadContract;

interface AssignedToLeadContract extends CreateNotificationOn
{
    /**
     * Assigned lead
     *
     * @return LeadContract
     */
    public function lead(): LeadContract;
}
