<?php

namespace App\Http\Responses\Notifications;

use Illuminate\Http\JsonResponse;

class ReadNotificationResponse extends JsonResponse
{
    public function __construct()
    {
        parent::__construct('OK', self::HTTP_NO_CONTENT);
    }
}
