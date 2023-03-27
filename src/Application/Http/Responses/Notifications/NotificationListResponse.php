<?php

namespace App\Http\Responses\Notifications;

use App\Http\Mappers\Notifications\ApiNotification;
use Domains\Notifications\Models\List\NotificationList;
use Domains\Notifications\Models\Notification;
use Illuminate\Http\JsonResponse;

class NotificationListResponse extends JsonResponse
{
    /**
     * @param NotificationList $notificationList
     * @throws \Exception
     */
    public function __construct(NotificationList $notificationList)
    {
        $data = [];
        $paginator = $notificationList->paginator();

        /** @var Notification $notification */
        foreach ($paginator->items() as $notification) {
            $data[] = (new ApiNotification($notification))->toArray();
        }

        parent::__construct(
            [
                'current_page' => $paginator->page(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'data' => $data,
            ],
            self::HTTP_OK
        );
    }
}
