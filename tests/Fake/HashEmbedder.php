<?php

declare(strict_types=1);

namespace Displace\AI\Contracts\Tests\Fake;

use Displace\AI\Contracts\Embedder;

/**
 * Deterministic, model-free embedder: coordinates are derived from a
 * SHA-256 stream over the input text. Useless for semantics, perfect for
 * exercising the packed-output contract.
 */
final class HashEmbedder implements Embedder
{
    public function __construct(private readonly int $dimensions = 8) {}

    public function embed(string $text): string
    {
        $bytes = '';
        $counter = 0;

        while (\strlen($bytes) < $this->dimensions) {
            $bytes .= hash('sha256', $text . ':' . $counter++, true);
        }

        $floats = [];

        for ($i = 0; $i < $this->dimensions; $i++) {
            $floats[] = (\ord($bytes[$i]) - 127.5) / 127.5;
        }

        return pack('g*', ...$floats);
    }

    public function embedBatch(array $texts): string
    {
        return implode('', array_map($this->embed(...), $texts));
    }

    public function dimensions(): int
    {
        return $this->dimensions;
    }
}
