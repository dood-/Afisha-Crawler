<?php

declare(strict_types=1);

namespace AfishaCrawler;

use AfishaCrawler\Entity\Event;
use AfishaCrawler\Parsers\Parser;

require '../vendor/autoload.php';

class Crawler
{
    /** @var Parser[] */
    protected $parsers;

    public function addParser(Parser $parser): self
    {
        $this->parsers[] = $parser;
        return $this;
    }

    public function fetch(): array
    {
        return array_map(function (Parser $parser) {
            return $parser->getEvents();
        }, $this->parsers);
    }

    public function fetchAsync(): array
    {
        return $this->parallel_map(function (Parser $parser) {
            return $parser->getEvents();
        }, $this->parsers);
    }

    /**
     * @param array|Event[] $events
     * @return array
     */
    public static function mergeDuplicates(array $events): array
    {
        /** @var Event[] $results */
        $results = [];

        foreach ($events as $event) {
            $name = static::removeNonWordCharacters($event->getName());

            if(array_key_exists($name, $results)) {
                $results[$name]->addSessions($event->getSessions());
            } else {
                $results[$name] = $event;
            }
        }

        return array_values($results);
    }

    protected static function removeNonWordCharacters(string $str): string
    {
        preg_match_all('/[a-zа-яё]+/u', mb_strtolower($str), $arr);
        return implode($arr[0]);
    }

    protected function parallel_map(callable $func, array $items): array {
        $childPids = [];
        $result = [];

        foreach ($items as $i => $item) {
            $newPid = pcntl_fork();

            if ($newPid == -1) {
                die('Can\'t fork process');
            }

            if ($newPid) {
                $childPids[] = $newPid;

                if ($i == count($items) - 1) {
                    foreach ($childPids as $childPid) {
                        pcntl_waitpid($childPid, $status);
                        $result[] = static::getSharedData($childPid);
                    }
                }
            } else {
                $funcResult = $func($item);
                static::setSharedData(getmypid(), serialize($funcResult));
                exit(0);
            }
        }

        return $result;
    }

    protected static function getSharedData($key): array
    {
        $sharedId = shmop_open($key, 'a', 0, 0);
        $shareData = shmop_read($sharedId, 0, shmop_size($sharedId));
        $result = unserialize($shareData);
        shmop_delete($sharedId);
        shmop_close($sharedId);
        return $result;
    }

    protected static function setSharedData($key, string $data): void
    {
        $sharedId = shmop_open($key, 'c', 0644, strlen($data));
        shmop_write($sharedId, $data, 0);
    }
}