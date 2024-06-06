<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Symfony\Component\Console\Application;
use App\ConsoleCommand\CollectRatesCommand;
use App\ConsoleCommand\DisplayRatesCommand;
use App\ConsoleCommand\ConsumeRatesCommand;
use App\Service\ExchangeRateFetcher;
use App\Service\Cache;
use App\Service\MessageBroker;
use App\Service\Worker;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create instances
$fetcher = new ExchangeRateFetcher();
$cache = new Cache();
$broker = new MessageBroker();
$worker = new Worker($fetcher, $cache, $broker);

// Create console application
$application = new Application();
$application->add(new CollectRatesCommand($worker));
$application->add(new DisplayRatesCommand($cache));
$application->add(new ConsumeRatesCommand($broker, $cache));
$application->run();
