<?php

declare(strict_types=1);

namespace Displace\AI\Contracts;

/**
 * Turns text into dense vectors.
 *
 * Vectors are returned as **packed little-endian float32 binary strings** —
 * the output of `pack('g*', ...$floats)` — never as PHP float arrays. A PHP
 * array inflates every coordinate to a zval; a packed string is a single
 * contiguous buffer that flows straight into a {@see VectorIndex} (or across
 * an FFI boundary) with zero per-element overhead. Callers who need floats
 * can `unpack('g*', $vector)` (1-indexed) at the edge.
 *
 * Implementations decide pooling, normalization, and any instruction
 * prefixing internally; the contract only promises a vector of
 * `dimensions()` float32 coordinates per input text.
 */
interface Embedder
{
    /**
     * Embed a single text.
     *
     * @return string One packed little-endian float32 vector —
     *                `strlen()` is exactly `4 * $this->dimensions()`.
     */
    public function embed(string $text): string;

    /**
     * Embed a batch of texts.
     *
     * Returns the vectors concatenated in input order — packed batches
     * compose by plain string concatenation, so the result feeds
     * {@see VectorIndex::add()} directly. `strlen()` is exactly
     * `4 * $this->dimensions() * count($texts)`.
     *
     * Implementations are free to process the batch however is fastest
     * (single forward pass, one network call, a loop); the contract only
     * fixes the output layout.
     *
     * @param list<string> $texts
     */
    public function embedBatch(array $texts): string;

    /**
     * Number of float32 coordinates per vector. Constant for the lifetime
     * of the embedder instance.
     */
    public function dimensions(): int;
}
