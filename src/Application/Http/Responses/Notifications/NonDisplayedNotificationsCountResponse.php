<?php

namespace App\Http\Responses\Notifications;

use Illuminate\Http\JsonResponse;

class NonDisplayedNotificationsCountResponse extends JsonResponse
{
    /**
     * @param int $nonDisplayedCount
     */
    public function __construct(int $nonDisplayedCount)
    {
        parent::__construct(
            [
                'count' => $nonDisplayedCount,
            ],
            self::HTTP_OK
        );
    }
}
