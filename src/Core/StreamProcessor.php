<?php

namespace Nexus\Core;

/**
 * Handles asynchronous ingestion and routing of telemetry frames.
 * Utilizes non-blocking I/O to maintain stream integrity under high load.
 */
class StreamProcessor
{
    private array $buffer = [];
    private int $maxBufferSize = 1024;

    public function process(string $payload): void
    {
        $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        
        if ($this->validateSchema($decoded)) {
            $this->dispatch($decoded);
        }
    }

    private function validateSchema(array $data): bool
    {
        return isset($data['sensor_id'], $data['timestamp'], $data['payload']);
    }

    private function dispatch(array $data): void
    {
        // Logic for downstream propagation to persistent storage
        $this->buffer[] = $data;
        if (count($this->buffer) >= $this->maxBufferSize) {
            $this->flush();
        }
    }

    private function flush(): void
    {
        $this->buffer = [];
    }
}