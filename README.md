```
$crawler = new Crawler;
$fetcher = new GuzzleFetcher;

$coliseum = new P24('https://api.kinobilety.net/api', $fetcher);
$coliseum->setMarket('698');

$kino = clone $coliseum;
$kino->setMarket('1');

$kinomax = new Kinomax('https://kinomax.ru', $fetcher);

$result = $crawler
    ->addParser($kino)
    ->addParser($coliseum)
    ->addParser($kinomax)
    ->fetchAsync();

$result = Crawler::mergeDuplicates(
    array_reduce($result, 'array_merge', []));

var_dump($result);
```