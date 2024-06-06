<?php

namespace App\ConsoleCommand;

use App\Service\Worker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectRatesCommand extends Command
{
    protected static $defaultName = 'app:collect-rates';

    private Worker $worker;

    public function __construct(Worker $worker)
    {
        parent::__construct();
        $this->worker = $worker;
    }

    protected function configure(): void
    {
        $this->setDescription('Collect exchange rates for the last 180 days.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->worker->collectDataForLast180Days();
            $output->writeln('Data collection completed successfully.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
