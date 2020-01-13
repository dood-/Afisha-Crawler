<?php

namespace AfishaCrawler\Parsers;



use AfishaCrawler\Entity\Event;

interface Parser
{
    /**
     * @return array|Event[]
     */
    public function getEvents(): array;
}