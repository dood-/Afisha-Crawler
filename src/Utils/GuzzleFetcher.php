<?php

declare(strict_types=1);

namespace AfishaCrawler\Utils;


use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class GuzzleFetcher implements Fetcher
{
    protected $headers = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
    ];

    /** @var Client */
    protected $client;

    public function __construct(string $baseUrl = null)
    {
        $this->client = new Client(['base_uri' => $baseUrl]);
    }

    public function get(string $uri, array $options = []): Crawler
    {
        $options = array_merge([
            'headers' => $this->headers,
        ], $options);

//        $contents = $this->fileCache(function ($uri, $options) {
//            // todo handle errors
//            $response = $this->client->get($uri, $options);
//            $contents = $response->getBody()->getContents();
//            return $contents;
//        })($uri, $options);

        $response = $this->client->get($uri, $options);
        $contents = $response->getBody()->getContents();

        return new Crawler($contents);
    }

    private function fileCache(callable $func) {
        return function() use ($func) {
            $args = func_get_args();
            $file = __DIR__ . '/../cache/' . md5(serialize($args));
            if (file_exists($file)) {
                return unserialize(file_get_contents($file));
            } else {
                $value = call_user_func_array($func, $args);
                file_put_contents($file, serialize($value));
                return $value;
            }
        };
    }
}