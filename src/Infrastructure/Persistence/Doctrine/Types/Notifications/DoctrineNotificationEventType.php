<?php

namespace Infrastructure\Persistence\Doctrine\Types\Notifications;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Domains\Notifications\Models\NotificationEventType;

class DoctrineNotificationEventType extends StringType
{
    public const NAME = 'event_type';

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
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return (string)$value;
    }

    /**
     * {@inheritdoc}
     * @return NotificationEventType
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): NotificationEventType
    {
        return new NotificationEventType($value);
    }
}
