<?php

namespace Domains\Common\Events;

use Domains\Common\Events\Integrations\IntegrationErrorOccurredContract;
use Domains\Common\Events\Notifications\AssignedToFollowupContract;
use Domains\Common\Events\Notifications\AssignedToLeadContract;
use Domains\Common\Events\Sequences\SequenceContactRepliedContract;
use Domains\Common\Events\Sequences\SequenceStoppedContract;
use Domains\Common\Models\Imports\ImportFinishedContract;
use Domains\Marketing\Models\Sequence\Contact\SequenceContact;
use Domains\Marketing\Models\Sequence\Sequence;
use Domains\Marketing\Repositories\SequenceRepositoryContract;
use Domains\Notifications\Models\Notification;
use Domains\Notifications\Repositories\NotificationRepositoryContract;
use Illuminate\Support\Facades\Log;

/**
 * Subscriber that allows event driven communication between different bounded contexts
 */
class CrossDomainSubscriber
{
    private array $listen = [
        AssignedToFollowupContract::class => [
            __CLASS__ . '@notifyUserAssignedToFollowup',
        ],
        AssignedToLeadContract::class => [
            __CLASS__ . '@notifyUserLeadAssigned',
        ],
        ImportFinishedContract::class => [
            __CLASS__ . '@notifyUserImportFinished',
        ],
        IntegrationErrorOccurredContract::class => [
            __CLASS__ . '@deactivateSequencesOnIntegrationError',
            __CLASS__ . '@notifyUserIntegrationErrorOccurred',
        ],
        SequenceContactRepliedContract::class => [
            __CLASS__ . '@notifyUserSequenceContactReplied',
        ],
        SequenceStoppedContract::class => [
            __CLASS__ . '@notifyUserSequenceStopped',
        ],
    ];

    /**
     * Handle even passed from any domain
     * @param DomainEvent $event
     */
    public function handleEvent(DomainEvent $event): void
    {
        $listeners = $this->getListeners($event);
        if (!empty($listeners)) {
            foreach ($listeners as $listener) {
                $tmp = explode('@', $listener);
                $subscriber = new $tmp[0]();
                $subscriber->{$tmp[1]}($event);
            }
        }
    }

    /**
     * @param IntegrationErrorOccurredContract $event
     */
    public function deactivateSequencesOnIntegrationError(IntegrationErrorOccurredContract $event): void
    {
        /** @var SequenceRepositoryContract $sequenceRepository */
        $sequenceRepository = app(SequenceRepositoryContract::class);

        $companyAccountId = $event->integration()->userCompanyAccount()->companyAccount()->uuid();
        $userId = $event->integration()->userCompanyAccount()->user()->uuid();

        $list = $sequenceRepository->findActiveByUserCompany($userId, $companyAccountId);

        try {
            /** @var Sequence $sequence */
            foreach ($list as $sequence) {
                $sequence->applyIntegrationErrorOccurred($sequenceRepository, $event);
            }
        } catch (\Exception $e){
            Log::channel('stderr')->error(
                'Error when deactivating sequence on integration error',
                [
                    'class' => __CLASS__,
                    'integration_id' => $event->integration()->uuid(),
                    'exception' => [
                        'code' => $e->getCode(),
                        'file'=> $e->getFile(),
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ],
                ]
            );
        }
    }


    /**
     * @param AssignedToFollowupContract $event
     * @throws \Exception
     */
    public function notifyUserAssignedToFollowup(AssignedToFollowupContract $event): void
    {
        $notificationRepository = app(NotificationRepositoryContract::class);
        $notification = Notification::onAssignedToFollowup($notificationRepository, $event);

        if ($notification) {
            $notificationRepository->flush();
        }
    }

    /**
     * @param AssignedToLeadContract $event
     * @throws \Exception
     */
    public function notifyUserLeadAssigned(AssignedToLeadContract $event): void
    {
        $notificationRepository = app(NotificationRepositoryContract::class);
        $notification = Notification::onAssignedToLead($event, $notificationRepository);

        if ($notification) {
            $notificationRepository->flush();
        }
    }

    /**
     * @param SequenceContactRepliedContract $event
     * @throws \Exception
     */
    public function notifyUserSequenceContactReplied(SequenceContactRepliedContract $event): void
    {
        $notificationRepository = app(NotificationRepositoryContract::class);
        $notification = Notification::onSequenceContactReplied($notificationRepository, $event);

        if ($notification) {
            $notificationRepository->flush();
        }
    }

    /**
     * @param SequenceStoppedContract $event
     * @throws \Exception
     */
    public function notifyUserSequenceStopped(SequenceStoppedContract $event): void
    {
        $notificationRepository = app(NotificationRepositoryContract::class);
        $notification = Notification::onSequenceStopped($notificationRepository, $event);

        if ($notification) {
            $notificationRepository->flush();
        }
    }

    /**
     * @param IntegrationErrorOccurredContract $event
     * @throws \Exception
     */
    public function notifyUserIntegrationErrorOccurred(IntegrationErrorOccurredContract $event): void
    {
        $notificationRepository = app(NotificationRepositoryContract::class);
        Notification::onIntegrationErrorOccurred($notificationRepository, $event);
        $notificationRepository->flush();
    }

    /**
     * @param ImportFinishedContract $event
     * @throws \Exception
     */
    public function notifyUserImportFinished(ImportFinishedContract $event): void
    {
        $notificationRepository = app(NotificationRepositoryContract::class);
        Notification::onImportFinished($notificationRepository, $event);
        $notificationRepository->flush();
    }

    /**
     * @param DomainEvent $event
     * @return array
     */
    private function getListeners(DomainEvent $event): array
    {
        $eventNames[] = get_class($event);
        $eventNames = array_merge($eventNames, array_values(class_implements($event)));

        $listeners = [];
        foreach ($eventNames as $eventName) {
            if ($eventListeners = ($this->listen[$eventName] ?? null)) {
                $listeners[] = $eventListeners;
            }
        }

        return array_merge([], ...$listeners);
    }
}
