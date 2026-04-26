<?php

namespace Nexus\Gateway;

use React\EventLoop\LoopInterface;
use Nexus\Protocol\ParserInterface;

/**
 * Orchestrates high-frequency telemetry ingestion from heterogenous hardware bridges.
 * Utilizes non-blocking I/O to maintain sub-millisecond latency under load.
 */
class StreamProcessor
{
    private LoopInterface $loop;
    private ParserInterface $parser;

    public function __construct(LoopInterface $loop, ParserInterface $parser)
    {
        $this->loop = $loop;
        $this->parser = $parser;
    }

    public function process(string $rawInput): void
    {
        $this->loop->futureTick(function () use ($rawInput) {
            try {
                $decoded = $this->parser->decode($rawInput);
                $this->dispatch($decoded);
            } catch (\InvalidArgumentException $e) {
                error_log("Telemetry ingestion fault: " . $e->getMessage());
            }
        });
    }

    private function dispatch(array $data): void
    {
        // Internal buffer persistence logic
    }
}