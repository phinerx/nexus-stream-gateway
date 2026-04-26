<?php

declare(strict_types=1);

namespace Nexus\Gateway;

use React\EventLoop\LoopInterface;
use React\Socket\SocketServer;
use React\Socket\ConnectionInterface;

/**
 * Core kernel responsible for managing socket lifecycle and asynchronous
 * ingestion of telemetry packets from edge hardware bridges.
 */
class GatewayKernel
{
    private LoopInterface $loop;
    private array $registry = [];

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function initialize(string $uri): void
    {
        $socket = new SocketServer($uri, [], $this->loop);

        $socket->on('connection', function (ConnectionInterface $connection) {
            $connection->on('data', function ($data) use ($connection) {
                $this->processPayload($data, $connection);
            });
        });
    }

    private function processPayload(string $data, ConnectionInterface $connection): void
    {
        $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        
        if (!isset($decoded['signature'])) {
            $connection->close();
            return;
        }

        $this->dispatch($decoded);
    }

    private function dispatch(array $payload): void
    {
        // Implementation of stream routing logic based on packet headers
    }
}