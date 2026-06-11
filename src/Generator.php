<?php

declare(strict_types=1);

namespace Displace\AI\Contracts;

/**
 * Text-in, text-out language-model generation.
 *
 * This is deliberately the lowest common denominator: a prompt string and
 * an options array. Chat-message abstractions, prompt builders, tool
 * calling, and streaming are framework territory — frameworks that want
 * them keep their own richer surface and adapt it down to this contract.
 *
 * Reasoning-model output handling is an implementation concern: a driver
 * SHOULD return the final answer with any `<think>...</think>` scratch
 * work already stripped.
 */
interface Generator
{
    /**
     * Generate a completion for `$prompt`.
     *
     * Recognised option keys — implementations MUST silently ignore keys
     * they do not understand, and callers MUST NOT rely on unlisted keys
     * being portable:
     *
     * - `max_tokens`  (int)   Generation budget.
     * - `temperature` (float) Sampling temperature; `0.0` requests
     *                         deterministic decoding where supported.
     * - `seed`        (int)   Sampler seed, where supported.
     *
     * @param array<string, mixed> $options
     *
     * @return string The generated text.
     */
    public function generate(string $prompt, array $options = []): string;
}
