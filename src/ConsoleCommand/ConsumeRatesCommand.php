<?php

namespace App\ConsoleCommand;

use App\Service\MessageBroker;
use App\Service\Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeRatesCommand extends Command
{
    protected static $defaultName = 'app:consume-rates';

    private MessageBroker $broker;
    private Cache $cache;

    public function __construct(MessageBroker $broker, Cache $cache)
    {
        parent::__construct();
        $this->broker = $broker;
        $this->cache = $cache;
    }

    protected function configure(): void
    {
        $this->setDescription('Consume exchange rates messages from RabbitMQ.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $callback = function ($msg) use ($output) {
            $message = json_decode($msg->body, true);
            $date = $message['date'];
            $rates = $message['rates'];

            // Cache the received rates
            $this->cache->set('rates_' . $date, json_encode($rates));

            $output->writeln("Cached rates for $date");
        };

        try {
            $this->broker->receiveMessages($callback);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
