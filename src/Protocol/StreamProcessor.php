<?php

namespace Nexus\Gateway\Protocol;

/**
 * Handles the normalization of incoming binary stream packets into structured
 * JSON entities before dispatching to the persistence layer.
 */
class StreamProcessor
{
    private const BUFFER_LIMIT = 4096;

    public function processPayload(string $rawInput): array
    {
        if (strlen($rawInput) > self::BUFFER_LIMIT) {
            throw new \RuntimeException('Payload exceeds defined frame buffer capacity.');
        }

        $decoded = json_decode($rawInput, true, 512, JSON_THROW_ON_ERROR);
        
        return $this->normalize($decoded);
    }

    private function normalize(array $data): array
    {
        return [
            'timestamp' => microtime(true),
            'origin'    => $data['node_id'] ?? 'unknown',
            'payload'   => $data['data'] ?? [],
            'checksum'  => hash('sha256', serialize($data))
        ];
    }
}