<?php

namespace Domains\Notifications\Models;

use Domains\Common\Events\Integrations\IntegrationErrorOccurredContract;
use Domains\Common\Events\Notifications\AssignedToFollowupContract;
use Domains\Common\Events\Notifications\AssignedToLeadContract;
use Domains\Common\Events\Sequences\SequenceContactRepliedContract;
use Domains\Common\Events\Sequences\SequenceStoppedContract;
use Domains\Common\Models\Imports\ImportFinishedContract;
use Domains\Common\Models\ValueObject;

class NotificationVariables implements ValueObject
{
    private array $items;

    /**
     * @param array $items
     */
    private function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @param AssignedToFollowupContract $event
     * @return static
     */
    public static function fromAssignedToFollowup(AssignedToFollowupContract $event): self
    {
        $calendarEvent = $event->event();

        $self = new self();
        $self->add('event_id', (string)$calendarEvent->uuid());
        $self->add('event_title', $calendarEvent->title());
        $self->add('event_type', $calendarEvent->type()->type());

        return $self;
    }

    /**
     * @param ImportFinishedContract $event
     * @return static
     */
    public static function fromImportFinished(ImportFinishedContract $event): self
    {
        $importJob = $event->importJob();

        $self = new self();
        $self->add('import_job_id', (string)$importJob->uuid());
        $self->add('import_type', $importJob->type()->type());
        $self->add('file_name', $importJob->sourceFile()->originalName());
        $self->add('entries', $importJob->entries());
        $self->add('errors', $importJob->errors());

        return $self;
    }

    /**
     * @param IntegrationErrorOccurredContract $event
     * @return static
     */
    public static function fromIntegrationErrorOccurred(IntegrationErrorOccurredContract $event): self
    {
        $integration = $event->integration();

        $self = new self();
        $self->add('integration_id', (string)$integration->uuid());
        $self->add('integration_service', $integration->service());
        $self->add('integration_status', $integration->status()->status());
        $self->add('integration_status_info', $integration->status()->additionalInfo());

        return $self;
    }

    /**
     * @param AssignedToLeadContract $event
     * @return static
     */
    public static function fromAssignedToLead(AssignedToLeadContract $event): self
    {
        $lead = $event->lead();
        $createdBy = $event->createdBy();

        $self = new self();
        $self->add('entity_id', (string)$lead->uuid());
        $self->add('entity_title', $lead->title());
        $self->add('user_full_name', $createdBy->displayName());
        $self->add('user_profile_picture', $createdBy->profilePicture()?->file()->directUrl()?->url());
        $self->add('user_id', (string)$event->target()->user()->uuid());

        return $self;
    }

    /**
     * @param SequenceContactRepliedContract $event
     * @return static
     */
    public static function fromSequenceContactReplied(SequenceContactRepliedContract $event): self
    {
        $sequenceContact = $event->performedOn();

        $self = new self();
        $self->add('sequence_contact_id', (string)$sequenceContact->uuid());
        $self->add('contact_id', (string)$sequenceContact->contact()->uuid());
        $self->add('contact_full_name', $sequenceContact->contact()->displayName());
        $self->add('contact_profile_picture', $sequenceContact->contact()?->photoFile()?->directUrl()?->url());
        $self->add('sequence_id', (string)$sequenceContact->sequence()->uuid());
        $self->add('sequence_name', $sequenceContact->sequence()->name());

        return $self;
    }

    /**
     * @param SequenceStoppedContract $event
     * @return static
     */
    public static function fromSequenceStopped(SequenceStoppedContract $event): self
    {
        $sequence = $event->performedOn();

        $self = new self();
        $self->add('sequence_id', (string)$sequence->uuid());
        $self->add('sequence_name', $sequence->name());

        return $self;
    }

    /**
     * @param string $key
     * @param string|null $value
     * @return $this
     */
    public function add(string $key, ?string $value = null): self
    {
        $this->items[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->items();
    }

    /**
     * @inheritDoc
     */
    public static function fromRawData(array $data): static
    {
        return new self($data);
    }

    /**
     * @inheritDoc
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_HEX_TAG);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
