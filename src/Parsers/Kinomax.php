<?php

declare(strict_types=1);

namespace AfishaCrawler\Parsers;


use AfishaCrawler\Entity\AgeRestriction;
use AfishaCrawler\Entity\Event;
use AfishaCrawler\Entity\EventSession;
use DateTimeImmutable;
use Symfony\Component\DomCrawler\Crawler;

class Kinomax extends BaseParser
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
        return $this->fetcher->get("$this->url/schedule/cinema?city=22&cinema=28&date=$date")
            ->filter('.cinema-schedule .film')
            ->each(function (Crawler $film) use ($date) {

                $filmUri = $film
                    ->filter('a')
                    ->reduce(function (Crawler $link) {
                        $href = $link->attr('href');

                        if(empty($href)) {
                            return false;
                        }

                        return preg_match('/^\/filmdata\/\d+$/', $href);
                    })
                    ->first()
                    ->attr('href');

                $event = $this->getFilm($this->url . $filmUri . '?city=22');

                $event->addSessions($this->sessionsExtractor($film, $date));

                return $event;
            });
    }

    protected function getFilm(string $url): Event
    {
        echo '[Kinomax] Get film: ' . $url . PHP_EOL;

        $page = $this->fetcher->get($url);

        $nameNode = $page->filter('.movie-name')->first();
        $name = trim($nameNode->filter('strong')->first()->text());
        $desc = trim($page->filter('.premiere-block')->parents()->nextAll()->text());
        $age = new AgeRestriction(trim($nameNode->filter('.film-rating span')->first()->text()));

        return new Event($name, $desc, $age);
    }

    protected function getDates(): array
    {
        return $this->fetcher->get($this->url . '/kirov?city=22')
            ->filter('.schedule-dates-panel .date')
            ->each(function (Crawler $date) {
                return $date->attr('data-date');
            });
    }

    protected function sessionsExtractor(Crawler $film, string $date): array
    {
        return $film->filter('.session')
            ->reduce(function (Crawler $session) {
                $class = $session->attr('class');
                return mb_stripos($class, 'session-disabled') === false;
            })
            ->each(function (Crawler $session) use ($date) {
                $time = trim($session->filter('a')->first()->text());
                $cost = trim($session->filter('div')->last()->text());

                $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i', "$date $time");

                return new EventSession($dt, 'Киномакс', $cost);
            });
    }
}