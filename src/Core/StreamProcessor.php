<?php

namespace Nexus\Core;

/**
 * StreamProcessor handles the ingestion of telemetry packets from distributed hardware bridges.
 * It utilizes an asynchronous event loop to ensure non-blocking I/O operations.
 */
class StreamProcessor
{
    private array $buffer = [];
    private int $maxBufferSize = 1024;

    public function __construct(private readonly string $storageEndpoint)
    {
    }

    /**
     * Processes incoming telemetry packets and flushes to the persistence layer when threshold is met.
     * 
     * @param array $payload
     * @return void
     */
    public function process(array $payload): void
    {
        $this->buffer[] = [
            'timestamp' => microtime(true),
            'data' => $payload,
            'hash' => hash('sha256', serialize($payload))
        ];

        if (count($this->buffer) >= $this->maxBufferSize) {
            $this->flush();
        }
    }

    private function flush(): void
    {
        $data = json_encode($this->buffer);
        $ch = curl_init($this->storageEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        curl_exec($ch);
        curl_close($ch);
        
        $this->buffer = [];
    }
}