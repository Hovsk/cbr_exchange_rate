<?php

namespace App\Service;

use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessageBroker
{
    private AMQPStreamConnection $connection;
    private AbstractChannel $channel;
    private string $queue;

    public function __construct()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                $_ENV['RABBITMQ_HOST'],
                $_ENV['RABBITMQ_PORT'],
                $_ENV['RABBITMQ_USER'],
                $_ENV['RABBITMQ_PASS']
            );
            $this->channel = $this->connection->channel();
            $this->queue = $_ENV['RABBITMQ_QUEUE'];
            $this->channel->queue_declare($this->queue, false, true, false, false);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to connect to RabbitMQ: ' . $e->getMessage());
        }
    }

    public function sendMessage(mixed $message): void
    {
        try {
            $msg = new AMQPMessage(json_encode($message), ['delivery_mode' => 2]);
            $this->channel->basic_publish($msg, '', $this->queue);
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to send message to RabbitMQ: ' . $e->getMessage());
        }
    }

    public function receiveMessages(callable $callback): void
    {
        try {
            $this->channel->basic_consume($this->queue, '', false, true, false, false, $callback);

            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to receive messages from RabbitMQ: ' . $e->getMessage());
        }
    }
}
