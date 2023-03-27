<?php

namespace Infrastructure\Persistence\Doctrine\Types\Notifications;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Domains\Notifications\Models\NotificationSource;

class DoctrineNotificationSource extends JsonType
{
    public const NAME = 'source';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['jsonb'] = true;

        return parent::getSQLDeclaration($column, $platform);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return $value->toJson();
    }

    /**
     * {@inheritdoc}
     * @return ?NotificationSource
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?NotificationSource
    {
        $data = parent::convertToPHPValue($value, $platform);
        if ($data === null) {
            return null;
        }

        return NotificationSource::fromRawData($data);
    }
}
