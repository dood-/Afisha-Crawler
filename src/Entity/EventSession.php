<?php

declare(strict_types=1);

namespace AfishaCrawler\Entity;


use DateTimeImmutable;

class EventSession
{
    /** @var DateTimeImmutable */
    private $datetime;

    /** @var string|null */
    private $cost;

    /** @var string */
    private $place;

    /**
     * EventSession constructor.
     * @param DateTimeImmutable $datetime
     * @param string $place
     * @param string $cost
     */
    public function __construct(DateTimeImmutable $datetime, string $place, ?string $cost = null)
    {
        $this->datetime = $datetime;
        $this->place = $place;
        $this->cost = $cost;
    }

    public function getDatetime(): DateTimeImmutable
    {
        return $this->datetime;
    }

    public function getCost(): string
    {
        return $this->cost;
    }

    /**
     * @return string
     */
    public function getPlace(): string
    {
        return $this->place;
    }
}