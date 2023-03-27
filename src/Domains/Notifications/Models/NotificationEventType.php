<?php

namespace Domains\Notifications\Models;

use Domains\Common\Models\ValueObject;

class NotificationEventType implements ValueObject
{
    private const TYPE_ASSIGNED_TO_FOLLOWUP = 'assigned_to_followup';
    private const TYPE_IMPORT_FINISHED = 'import_finished';
    private const TYPE_INTEGRATION_ERROR_OCCURRED = 'integration_error_occurred';
    private const TYPE_LEAD_ASSIGNED_TO = 'lead_assigned_to';
    private const TYPE_SEQUENCE_CONTACT_REPLIED = 'sequence_contact_replied';
    private const TYPE_SEQUENCE_STOPPED = 'sequence_stopped';

    private const ALL_TYPES = [
        self::TYPE_ASSIGNED_TO_FOLLOWUP,
        self::TYPE_IMPORT_FINISHED,
        self::TYPE_INTEGRATION_ERROR_OCCURRED,
        self::TYPE_LEAD_ASSIGNED_TO,
        self::TYPE_SEQUENCE_CONTACT_REPLIED,
        self::TYPE_SEQUENCE_STOPPED,
    ];

    private string $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (!in_array($type, self::ALL_TYPES)) {
            throw new \InvalidArgumentException(sprintf('Type %s not supported', $type));
        }
        $this->type = $type;
    }

    /**
     * @return static
     */
    public static function assignedToFollowup(): self
    {
        return new self(self::TYPE_ASSIGNED_TO_FOLLOWUP);
    }

    /**
     * @return static
     */
    public static function integrationErrorOccurred(): self
    {
        return new self(self::TYPE_INTEGRATION_ERROR_OCCURRED);
    }

    /**
     * @return static
     */
    public static function importFinished(): self
    {
        return new self(self::TYPE_IMPORT_FINISHED);
    }

    /**
     * @return static
     */
    public static function leadAssignedTo(): self
    {
        return new self(self::TYPE_LEAD_ASSIGNED_TO);
    }

    /**
     * @return static
     */
    public static function sequenceContactReplied(): self
    {
        return new self(self::TYPE_SEQUENCE_CONTACT_REPLIED);
    }

    /**
     * @return static
     */
    public static function sequenceStopped(): self
    {
        return new self(self::TYPE_SEQUENCE_STOPPED);
    }

    /**
     * @param NotificationEventType $oType
     * @return bool
     */
    public function same(NotificationEventType $oType): bool
    {
        return $this->type() === $oType->type();
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type(),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromRawData(array $data): static
    {
        return new self($data['type']);
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
