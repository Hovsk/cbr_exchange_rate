# CBR Exchange Rate Fetcher

This project fetches exchange rates from the Central Bank of Russia, caches them, and calculates the difference from the previous trading day. It also demonstrates the use of message brokers and includes a worker to collect data for the past 180 days using a console command.

## Requirements

- PHP 8.1+
- Composer
- Redis
- RabbitMQ

## Installation

1. **Clone the Repository**

   ```sh
   git clone https://github.com/Hovsk/cbr_exchange_rate.git
   
2. **Navigate to the Project Directory**

   ```sh
   cd cbr_exchange_rate
   
3. **Install Dependencies**

   ```sh
   composer install
   
4. **Configure Environment Variables**

   ```sh
   Create a .env file and add variables from `env.example`

5. **Run Redis Server**

   ```sh
   redis-server
   
6. **Run RabbitMQ Server**

   ```sh
   rabbitmq-server
   
## Usage

**Collect Exchange Rates for the Last 180 Days**

Run the following console command to collect exchange rates for the past 180 days and cache them:

```sh
php bin/console app:collect-rates
```

**Consume Messages from RabbitMQ**

```sh
php bin/console app:consume-rates
```

After these two you can run the following command to 
**Display Exchange Rates and Differences**
```sh
php bin/console app:display-rates 08-02-2024 
```


