<?php

namespace Infrastructure\Persistence\Doctrine\Types\Notifications;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use Domains\Notifications\Models\NotificationId;

class DoctrineNotificationId extends GuidType
{
    public const NAME = 'notification_id';

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (is_null($value)) {
            return null;
        }
        return (string)$value;
    }

    /**
     * {@inheritdoc}
     * @return NotificationId|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?NotificationId
    {
        if (is_null($value)) {
            return null;
        }
        return new NotificationId($value);
    }
}
