<?php

declare(strict_types=1);

namespace AfishaCrawler\Parsers;


use AfishaCrawler\Utils\Fetcher;

abstract class BaseParser implements Parser
{
    /** @var string */
    protected $url;

    /** @var Fetcher */
    protected $fetcher;

    public function __construct(string $url, Fetcher $fetcher)
    {
        $this->url = rtrim($url, '/?');
        $this->fetcher = $fetcher;
    }

    protected static function flat_map(callable $callback, array $items): array
    {
        return array_reduce(array_map($callback, $items), 'array_merge', []);
    }
}