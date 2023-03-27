<?php

namespace Infrastructure\Persistence\Doctrine\Types\Notifications;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\JsonType;
use Domains\Notifications\Models\NotificationVariables;

class DoctrineNotificationVariables extends JsonType
{
    public const NAME = 'variables';

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
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        /** @var NotificationVariables $value */
        return $value->toJson();
    }

    /**
     * {@inheritdoc}
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?NotificationVariables
    {
        $data = parent::convertToPHPValue($value, $platform);
        if ($data === null) {
            return null;
        }

        return NotificationVariables::fromRawData($data);
    }
}
