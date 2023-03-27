<?php

namespace Domains\Notifications\Models;

use Domains\Common\Events\Integrations\IntegrationErrorOccurredContract;
use Domains\Common\Events\Notifications\AssignedToFollowupContract;
use Domains\Common\Events\Notifications\AssignedToLeadContract;
use Domains\Common\Events\Sequences\SequenceContactRepliedContract;
use Domains\Common\Events\Sequences\SequenceStoppedContract;
use Domains\Common\Models\Account\UserCompanyAccountContract;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Imports\ImportFinishedContract;
use Domains\Notifications\Repositories\NotificationRepositoryContract;

class Notification extends AggregateRoot
{
    private NotificationSource $source;
    private NotificationEventType $eventType;
    private NotificationVariables $variables;
    private UserCompanyAccountContract $target;
    private ?UserContract $createdBy;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;
    private ?\DateTimeImmutable $displayedAt;
    private ?\DateTimeImmutable $readAt;

    /**
     * @param NotificationId $id
     * @param NotificationSource $source
     * @param NotificationEventType $eventType
     * @param NotificationVariables $variables
     * @param UserCompanyAccountContract $target
     * @param UserContract $createdBy
     */
    private function __construct(
        NotificationId $id,
        NotificationSource $source,
        NotificationEventType $eventType,
        NotificationVariables $variables,
        UserCompanyAccountContract $target,
        UserContract $createdBy
    ) {
        parent::__construct($id);

        if ($createdBy->uuid()->equals(new AggregateRootId(UserContract::SYSTEM_ENTITY_ID))) {
            $createdBy = null;
        }

        $this->source = $source;
        $this->eventType = $eventType;
        $this->variables = $variables;
        $this->target = $target;
        $this->createdBy = $createdBy;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->displayedAt = null;
        $this->readAt = null;
    }

    /**
     * @param AssignedToLeadContract $event
     * @param NotificationRepositoryContract $repository
     * @return Notification|null
     * @throws \Exception
     */
    public static function onAssignedToLead(
        AssignedToLeadContract $event,
        NotificationRepositoryContract $repository
    ): ?self {
        if ($event->createdBy() === $event->target()->user()) {
            return null;
        }

        $notification = new self(
            NotificationId::nextId(),
            NotificationSource::lead($event->lead()),
            NotificationEventType::leadAssignedTo(),
            NotificationVariables::fromAssignedToLead($event),
            $event->target(),
            $event->createdBy()
        );

        $repository->store($notification);

        return $notification;
    }

    /**
     * @param NotificationRepositoryContract $repository
     * @param SequenceContactRepliedContract $event
     * @return Notification|null
     * @throws \Exception
     */
    public static function onSequenceContactReplied(
        NotificationRepositoryContract $repository,
        SequenceContactRepliedContract $event
    ): ?self {
        # Skip if the event was triggered by the user, not by the system
        if (!$event->createdBy()->uuid()->equals(new AggregateRootId(UserContract::SYSTEM_ENTITY_ID))) {
            return null;
        }

        $notification = new self(
            NotificationId::nextId(),
            NotificationSource::sequenceContact($event->performedOn()),
            NotificationEventType::sequenceContactReplied(),
            NotificationVariables::fromSequenceContactReplied($event),
            $event->target(),
            $event->createdBy()
        );

        $repository->store($notification);

        return $notification;
    }

    /**
     * @param NotificationRepositoryContract $repository
     * @param SequenceStoppedContract $event
     * @return Notification|null
     * @throws \Exception
     */
    public static function onSequenceStopped(
        NotificationRepositoryContract $repository,
        SequenceStoppedContract $event
    ): ?self {
        # Skip if the event was triggered by the sequence owner
        if ($event->createdBy()->uuid()->equals($event->performedOn()->owner()->uuid())) {
            return null;
        }

        $notification = new self(
            NotificationId::nextId(),
            NotificationSource::sequence($event->performedOn()),
            NotificationEventType::sequenceStopped(),
            NotificationVariables::fromSequenceStopped($event),
            $event->target(),
            $event->createdBy()
        );

        $repository->store($notification);

        return $notification;
    }

    /**
     * @param NotificationRepositoryContract $repository
     * @param IntegrationErrorOccurredContract $event
     * @return Notification
     * @throws \Exception
     */
    public static function onIntegrationErrorOccurred(
        NotificationRepositoryContract $repository,
        IntegrationErrorOccurredContract $event
    ): self {
        $notification = new self(
            NotificationId::nextId(),
            NotificationSource::integration($event->integration()),
            NotificationEventType::integrationErrorOccurred(),
            NotificationVariables::fromIntegrationErrorOccurred($event),
            $event->target(),
            $event->createdBy()
        );

        $repository->store($notification);

        return $notification;
    }

    /**
     * @param NotificationRepositoryContract $repository
     * @param ImportFinishedContract $event
     * @return Notification
     * @throws \Exception
     */
    public static function onImportFinished(
        NotificationRepositoryContract $repository,
        ImportFinishedContract $event
    ): self {
        $notification = new self(
            NotificationId::nextId(),
            NotificationSource::importJob($event->importJob()),
            NotificationEventType::importFinished(),
            NotificationVariables::fromImportFinished($event),
            $event->target(),
            $event->createdBy()
        );

        $repository->store($notification);

        return $notification;
    }

    /**
     * @param NotificationRepositoryContract $repository
     * @param AssignedToFollowupContract $event
     * @return Notification|null
     * @throws \Exception
     */
    public static function onAssignedToFollowup(
        NotificationRepositoryContract $repository,
        AssignedToFollowupContract $event
    ): ?self {
        if ($event->createdBy() === $event->target()->user()) {
            return null;
        }

        $notification = new self(
            NotificationId::nextId(),
            NotificationSource::calendarEvent($event->event()),
            NotificationEventType::assignedToFollowup(),
            NotificationVariables::fromAssignedToFollowup($event),
            $event->target(),
            $event->createdBy()
        );

        $repository->store($notification);

        return $notification;
    }

    public function source(): NotificationSource
    {
        return $this->source;
    }

    public function eventType(): NotificationEventType
    {
        return $this->eventType;
    }

    public function variables(): NotificationVariables
    {
        return $this->variables;
    }

    public function target(): UserCompanyAccountContract
    {
        return $this->target;
    }

    public function createdBy(): ?UserContract
    {
        return $this->createdBy;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function displayedAt(): ?\DateTimeImmutable
    {
        return $this->displayedAt;
    }

    public function readAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function display(NotificationRepositoryContract $notificationRepository): void
    {
        if ($this->displayedAt) {
            return;
        }

        $this->displayedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->nextVersion();

        $notificationRepository->store($this);
    }

    public function read(NotificationRepositoryContract $notificationRepository): void
    {
        if ($this->readAt) {
            return;
        }

        $this->readAt = new \DateTimeImmutable();
        $this->displayedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->nextVersion();

        $notificationRepository->store($this);
    }
}
