<?php

namespace App\Service;

class Worker
{
    private ExchangeRateFetcher $fetcher;
    private Cache $cache;
    private MessageBroker $broker;

    public function __construct(ExchangeRateFetcher $fetcher, Cache $cache, MessageBroker $broker)
    {
        $this->fetcher = $fetcher;
        $this->cache = $cache;
        $this->broker = $broker;
    }

    /**
     * Collect exchange rate data for the last 180 days and cache it.
     */
    public function collectDataForLast180Days(): void
    {
        $endDate = new \DateTime();
        for ($i = 0; $i < 180; $i++) {
            $date = $endDate->modify('-1 day')->format('d/m/Y');
            try {
                // Check if rates for this date are already cached
                $cachedRates = $this->cache->get('rates_' . $date);
                if ($cachedRates) {
                    echo "Rates for $date are already cached.\n";
                    continue;
                }

                $rates = $this->fetcher->fetchRates($date);
                $this->broker->sendMessage(['date' => $date, 'rates' => $rates]);
            } catch (\RuntimeException $e) {
                echo "Error on $date: " . $e->getMessage() . "\n";
            }
        }
    }
}
