<?php

namespace Nexus\Gateway\Core;

use React\EventLoop\LoopInterface;
use Nexus\Gateway\Interfaces\ProcessorInterface;

/**
 * Handles high-concurrency ingestion of hardware telemetry signals.
 * Utilizes non-blocking I/O to maintain zero-copy buffer efficiency.
 */
class StreamProcessor implements ProcessorInterface
{
    private LoopInterface $loop;
    private array $buffer = [];

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function ingest(string $payload): void
    {
        $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        
        if ($this->validateSchema($data)) {
            $this->buffer[] = $data;
            $this->processQueue();
        }
    }

    private function validateSchema(array $data): bool
    {
        return isset($data['sensor_id'], $data['timestamp'], $data['payload']);
    }

    private function processQueue(): void
    {
        $this->loop->futureTick(function () {
            while ($item = array_shift($this->buffer)) {
                // Direct memory-mapped persistence logic here
            }
        });
    }
}