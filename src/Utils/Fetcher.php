<?php

declare(strict_types=1);

namespace AfishaCrawler\Utils;


use Symfony\Component\DomCrawler\Crawler;

interface Fetcher
{
    public function get(string $uri, array $options = []): Crawler;

    public function getJson(string $uri, array $options = []): array;
}