<?php

declare(strict_types=1);

namespace AfishaCrawler\Entity;


class Event
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $desc;

    /** @var AgeRestriction|null */
    private $ageRestriction;

    /** @var array|EventSession[] */
    private $sessions;

    /**
     * Event constructor.
     * @param string $name
     * @param string|null $desc
     * @param AgeRestriction|null $ageRestriction
     * @param array|null $sessions
     */
    public function __construct(string $name, ?string $desc, ?AgeRestriction $ageRestriction, array $sessions = [])
    {
        $this->name = $name;
        $this->desc = $desc;
        $this->ageRestriction = $ageRestriction;
        $this->sessions = $sessions;
    }

    /**
     * @param array|EventSession $sessions
     */
    public function addSessions(array $sessions): void
    {
        foreach ($sessions as $session) {
            $this->sessions[] = $session;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDesc(): ?string
    {
        return $this->desc;
    }

    /**
     * @return AgeRestriction|null
     */
    public function getAgeRestriction(): ?AgeRestriction
    {
        return $this->ageRestriction;
    }

    /**
     * @return EventSession[]|array
     */
    public function getSessions()
    {
        return $this->sessions;
    }
}