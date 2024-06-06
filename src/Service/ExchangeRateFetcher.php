<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ExchangeRateFetcher
{
    private Client $client;
    private string $url;

    public function __construct()
    {
        $this->client = new Client();
        $this->url = $_ENV['CBR_API_URL'];
    }

    public function fetchRates(string $date): array
    {
        try {
            $response = $this->client->get($this->url, [
                'query' => ['date_req' => $date]
            ]);

            $xml = simplexml_load_string($response->getBody()->getContents());

            $rates = [];
            foreach ($xml->Valute as $valute) {
                $rates[(string)$valute->CharCode] = (float)str_replace(',', '.', (string)$valute->Value);
            }

            return $rates;
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to fetch rates from CBR: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \RuntimeException('An unexpected error occurred while fetching rates: ' . $e->getMessage());
        }
    }
}
