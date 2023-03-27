<?php

namespace Domains\Notifications\Models\List;

use Domains\Common\Models\PagedListFilters;

class NotificationListFilters extends PagedListFilters
{
    protected const SORT = [
        'created_at' => 'createdAt',
    ];

    private \DateTimeImmutable $dateFrom;
    private bool $excludeDisplayed;
    private bool $excludeRead;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $from = (new \DateTimeImmutable(date('Y-m-d 00:00:00')))->getTimestamp() - (30*24*60*60); //30 days
        $this->dateFrom = new \DateTimeImmutable('@' . (int)$from);

        $this->excludeDisplayed = false;
        $this->excludeRead = false;
    }

    /**
     * @param ?int $dateFrom
     * @return $this
     * @throws \Exception
     */
    public function setDateFrom(?int $dateFrom): self
    {
        if ($dateFrom) {
            $this->dateFrom = new \DateTimeImmutable('@' . $dateFrom);
        }

        return $this;
    }

    /**
     * @param bool $excludeDisplayed
     * @return NotificationListFilters
     */
    public function setExcludeDisplayed(bool $excludeDisplayed): self
    {
        $this->excludeDisplayed = $excludeDisplayed;

        return $this;
    }

    /**
     * @param bool $excludeRead
     * @return NotificationListFilters
     */
    public function setExcludeRead(bool $excludeRead): self
    {
        $this->excludeRead = $excludeRead;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function dateFrom(): \DateTimeImmutable
    {
        return $this->dateFrom;
    }

    /**
     * @return bool
     */
    public function excludeDisplayed(): bool
    {
        return $this->excludeDisplayed;
    }

    /**
     * @return bool
     */
    public function excludeRead(): bool
    {
        return $this->excludeRead;
    }
}
