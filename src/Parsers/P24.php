<?php

declare(strict_types=1);

namespace AfishaCrawler\Parsers;


use AfishaCrawler\Entity\AgeRestriction;
use AfishaCrawler\Entity\Event;
use AfishaCrawler\Entity\EventSession;
use DateTimeImmutable;
use Symfony\Component\DomCrawler\Crawler;

class P24 extends BaseParser
{
    /** @var string */
    protected $market = '1';

    /**
     * @inheritDoc
     */
    public function getEvents(): array
    {
        return array_reduce(
                array_map([$this, 'getFilm'], $this->getFilms()),
                'array_merge', []);
    }

    public function setMarket(string $val): void
    {
        $this->market = $val;
    }

    private function getFilms(): array
    {
        return $this->fetcher->get($this->url . '/getFilms?cityId=27&marketId=' . $this->market)
            ->filter('.film_list_item')
            ->each(function (Crawler $item) {
                $id = (int)preg_replace('/film_/', '', $item->attr('id'));
                return $this->url . "/getSchedule?cityId=27&filmId=$id&marketId=$this->market";
            });
    }

    /**
     * @param string $url
     * @return array|Event[]
     */
    private function getFilm(string $url): array
    {
        echo "[P24($this->market)] Get film $url" . PHP_EOL;
        return $this->fetcher->get($url)
            ->each(function (Crawler $item) {
                $name = $item->filter('.step1__film_info_title')->text();
                $desc = $item->filter('.step1__film_info .film__description_info .text')->text();
                $age = $item->filter('.step1__film_info_rating')
                    ->each(function (Crawler $age) {
                        return new AgeRestriction($age->text());
                    });

                $age = empty($age) ? null : reset($age);

                $sessions = $this->getSessions($item);

                return new Event($name, $desc, $age, $sessions);
            });
    }

    /**
     * @param Crawler $film
     * @return array|EventSession[]
     */
    private function getSessions(Crawler $film): array
    {
        $sessions = $film
            ->filter('.step1__seans_info_list')
            ->each($this->daysExtractor());

        return array_reduce($sessions, 'array_merge', []);
    }

    private function daysExtractor(): callable
    {
        return function (Crawler $day) {
            $date = $day->attr('data-date');
            $sessions = $day
                ->filter('.step1__seans_info')
                ->each($this->sessionsExtractor($date));
            return array_reduce($sessions, 'array_merge', []);
        };
    }

    private function sessionsExtractor(string $date): callable
    {
        return function (Crawler $cinema) use ($date) {
            $place = $cinema->filter('.step1__seans_info_theater')->text();

            return $cinema
                ->filter('.step1__seans_info_list_time .step1__seans_info_list_time_element')
                ->each(function (Crawler $timeItem) use ($place, $date) {
                    $dt = $this->parseSessionDt($date, $timeItem->text());
                    return new EventSession($dt, $place);
                });
        };
    }

    private function parseSessionDt(string $date, string $item): DateTimeImmutable
    {
        preg_match('/\d{2}:\d{2}/', $item, $time);
        $time = $time[0];
        $dt = DateTimeImmutable::createFromFormat('d.m.Y H:i', "$date $time");

        $hour = ((int)$dt->format('H'));

        if ($hour >= 0 && $hour < 4) {
            $dt = $dt->modify('+1 day'); // ночные сеансы выводятся сегодня, поэтому нужно прибавить один день
        }

        return $dt;
    }
}