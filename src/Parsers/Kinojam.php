<?php

declare(strict_types=1);

namespace AfishaCrawler\Parsers;


use AfishaCrawler\Entity\AgeRestriction;
use AfishaCrawler\Entity\Event;
use AfishaCrawler\Entity\EventSession;
use DateInterval;
use DateTime;
use DateTimeImmutable;

class Kinojam extends BaseParser
{
    /**
     * @inheritDoc
     */
    public function getEvents(): array
    {
        return static::flat_map(function ($date) {
            return $this->getFilms($date);
        }, $this->getDates());
    }

    protected function getFilms(string $date): array
    {
        echo '[Kinojam] Get films by date: ' . $date . PHP_EOL;

        $releases = $this->fetcher->getJson("$this->url/release/playbill?city_id=83&date=$date", [
            'headers' => [
                'x-application-token' => '08sSh6VKlsapX8N3MVedOsHRMuddKi1q',
                'x-platform' => 'widget',
                'content-type' => 'application/JSON'
            ],
        ])['releases'];

        $events = [];

        foreach ($releases as $release) {
            $event = $this->getFilm($release);
            $event->addSessions($this->sessionsExtractor($release['seances']));
            $events[] = $event;
        }

        return $events;
    }

    protected function getFilm(array $data): Event
    {
        $name = trim($data['title']);
        $desc = ' ';
        $age = new AgeRestriction(trim($data['age_rating']));

        return new Event($name, $desc, $age);
    }

    protected function getDates(): array
    {
        $format = 'Y-m-d';
        $date = new DateTime();
        $interval = new DateInterval('P1M');

        return [
            $date->format($format),
            $date->add($interval)->format($format),
            $date->add($interval)->format($format),
            $date->add($interval)->format($format),
        ];
    }

    protected function sessionsExtractor(array $seances): array
    {
        $result = [];

        foreach ($seances as $seance) {
            $dt = new DateTimeImmutable($seance['start_date_time']);
            $cost = (intval($seance['price']['min'])) / 100;
            $result[] = new EventSession($dt, 'КиноJam', "от $cost");
        }

        return $result;
    }
}