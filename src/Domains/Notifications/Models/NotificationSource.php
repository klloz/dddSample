<?php

namespace Domains\Notifications\Models;

use Domains\Common\Models\Calendar\EventContract;
use Domains\Common\Models\Imports\ImportJobContract;
use Domains\Common\Models\Integrations\IntegrationContract;
use Domains\Common\Models\Lead\LeadContract;
use Domains\Common\Models\Sequences\SequenceContactContract;
use Domains\Common\Models\Sequences\SequenceContract;
use Domains\Common\Models\ValueObject;

class NotificationSource implements ValueObject
{
    private const CALENDAR_EVENT = 'CalendarEvent';
    private const IMPORT_JOB = 'ImportJob';
    private const INTEGRATION = 'Integration';
    private const LEAD = 'Lead';
    private const SEQUENCE = 'Sequence';
    private const SEQUENCE_CONTACT = 'SequenceContact';

    private const ALL_TYPES = [
        self::CALENDAR_EVENT,
        self::IMPORT_JOB,
        self::INTEGRATION,
        self::LEAD,
        self::SEQUENCE,
        self::SEQUENCE_CONTACT,
    ];

    private string $type;
    private string $uuid;

    /**
     * @param string $type
     * @param string $uuid
     */
    private function __construct(string $type, string $uuid)
    {
        if (!in_array($type, self::ALL_TYPES)) {
            throw new \InvalidArgumentException(sprintf('Notification source %s not supported', $type));
        }

        $this->type = $type;
        $this->uuid = $uuid;
    }

    /**
     * @param ImportJobContract $importJob
     * @return static
     */
    public static function importJob(ImportJobContract $importJob): self
    {
        return new self(self::IMPORT_JOB, (string)$importJob->uuid());
    }

    /**
     * @param EventContract $event
     * @return static
     */
    public static function calendarEvent(EventContract $event): self
    {
        return new self(self::CALENDAR_EVENT, (string)$event->uuid());
    }

    /**
     * @param IntegrationContract $integration
     * @return static
     */
    public static function integration(IntegrationContract $integration): self
    {
        return new self(self::INTEGRATION, (string)$integration->uuid());
    }

    /**
     * @param LeadContract $lead
     * @return static
     */
    public static function lead(LeadContract $lead): self
    {
        return new self(self::LEAD, (string)$lead->uuid());
    }

    /**
     * @param SequenceContract $sequence
     * @return static
     */
    public static function sequence(SequenceContract $sequence): self
    {
        return new self(self::SEQUENCE, (string)$sequence->uuid());
    }

    /**
     * @param SequenceContactContract $sequenceContact
     * @return static
     */
    public static function sequenceContact(SequenceContactContract $sequenceContact): self
    {
        return new self(self::SEQUENCE_CONTACT, (string)$sequenceContact->uuid());
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function uuid(): string
    {
        return $this->uuid;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type(),
            'uuid' => $this->uuid(),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromRawData(array $data): static
    {
        return new self($data['type'], $data['uuid']);
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
        return $this->type();
    }
}
