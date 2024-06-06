<?php

namespace App\ConsoleCommand;

use App\Service\Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisplayRatesCommand extends Command
{
    public const DEFAULT_CURRENCY = 'USD';

    protected static $defaultName = 'app:display-rates';

    private Cache $cache;

    public function __construct(Cache $cache)
    {
        parent::__construct();
        $this->cache = $cache;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Display exchange rates for a specific date and currency.')
            ->addArgument(
                'date',
                InputArgument::OPTIONAL,
                'The date to display rates for (d/m/Y)',
                (new \DateTime())->format('d/m/Y')
            )
            ->addArgument(
                'currency',
                InputArgument::OPTIONAL,
                'The currency code to display rates for',
                self::DEFAULT_CURRENCY
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = (new \DateTime($input->getArgument('date')))->format('d/m/Y');
        $currencyCode = $input->getArgument('currency');

        try {
            $todayRates = $this->getRatesForDate($date);
            $previousRates = $this->getRatesForDate(
                (new \DateTime($input->getArgument('date')))
                    ->modify('-1 day')
                    ->format('d/m/Y')
            );

            if ($todayRates && $previousRates) {
                $rateToday = $todayRates[$currencyCode] ?? 'N/A';
                $ratePrevious = $previousRates[$currencyCode] ?? 'N/A';
                $difference = is_numeric($rateToday) && is_numeric($ratePrevious) ? $rateToday - $ratePrevious : 'N/A';
            } else {
                $rateToday = $difference = 'N/A';
            }

            $output->writeln("Exchange Rate for $currencyCode on $date:");
            $output->writeln("Rate: $rateToday");
            $output->writeln("Difference from previous day: $difference");

            return Command::SUCCESS;
        } catch (\RuntimeException $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function getRatesForDate(string $date): ?array
    {
        $ratesForDate = $this->cache->get('rates_' . $date);

        if (empty($ratesForDate)) {
            throw new \RuntimeException("Rates not found.");
        }

        return json_decode($ratesForDate, true);
    }

}
