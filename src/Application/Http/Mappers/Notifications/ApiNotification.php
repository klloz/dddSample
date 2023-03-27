<?php

namespace App\Http\Mappers\Notifications;

use App\Http\Mappers\Accounts\ApiProfilePicture;
use Domains\Notifications\Models\Notification;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *   schema="Notification",
 *   type="object",
 *   @OA\Property (
 *     property="id",
 *     type="string",
 *   ),
 *   @OA\Property (
 *     property="source",
 *     type="object",
 *     ref="#/components/schemas/NotificationSource"
 *   ),
 *   @OA\Property (
 *     property="event_type",
 *     type="string",
 *     enum={
 *       "lead_assigned_to",
 *       "followup_created",
 *     }
 *   ),
 *   @OA\Property (
 *     property="variables",
 *     type="object",
 *   ),
 *   @OA\Property (
 *     property="created_by",
 *     type="object",
 *     ref="#/components/schemas/NotificationCreatedBy"
 *   ),
 *   @OA\Property (
 *     property="created_at",
 *     type="integer",
 *   ),
 *   @OA\Property (
 *     property="displayed_at",
 *     type="integer",
 *   ),
 *   @OA\Property (
 *     property="read_at",
 *     type="integer",
 *   ),
 * ),
 *
 * @OA\Schema (
 *   schema="NotificationSource",
 *   type="object",
 *   @OA\Property (
 *     property="type",
 *     type="string",
 *     enum={
 *       "Lead",
 *     }
 *   ),
 *   @OA\Property (
 *     property="uuid",
 *     type="string",
 *   ),
 * ),
 *
 * @OA\Schema(
 *   schema="NotificationList",
 *   type="object",
 *   @OA\Property(
 *     property="current_page",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="last_page",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="per_page",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="total",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="data",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/Notification")
 *   ),
 * ),
 *
 * @OA\Schema (
 *   schema="NonDisplayedCount",
 *   type="object",
 *   @OA\Property (
 *     property="count",
 *     type="integer",
 *   ),
 * ),
 */
class ApiNotification
{
    /**
     * @param Notification $notification
     */
    public function __construct(private Notification $notification)
    {
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return [
            'id' => (string)$this->notification->uuid(),
            'source' => [
                'type' => $this->notification->source()->type(),
                'uuid' => $this->notification->source()->uuid(),
            ],
            'event_type' => (string)$this->notification->eventType(),
            'variables' =>  $this->notification->variables()->toArray(),
            'created_by' => $this->createdBy(),
            'created_at' => $this->notification->createdAt()->getTimestamp(),
            'displayed_at' => $this->notification->displayedAt()?->getTimestamp(),
            'read_at' => $this->notification->readAt()?->getTimestamp(),
        ];
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    private function createdBy(): ?array
    {
        $createdBy = $this->notification->createdBy();
        if (!$createdBy) {
            return null;
        }

        return [
            'id' => (string)$this->notification->createdBy()?->uuid(),
            'display_name' =>  $this->notification->createdBy()?->displayName(),
            'profile_picture' => (new ApiProfilePicture($createdBy->profilePicture()))->toArray(),
        ];
    }
}
