<?php

declare(strict_types=1);

namespace Displace\AI\Contracts\Tests\Fake;

use Displace\AI\Contracts\Generator;

/**
 * Deterministic generator: echoes the prompt's words back, treating each
 * word as one "token" so the max_tokens option is observable in tests.
 */
final class EchoGenerator implements Generator
{
    public function generate(string $prompt, array $options = []): string
    {
        $words = preg_split('/\s+/', trim($prompt), -1, PREG_SPLIT_NO_EMPTY);
        $words = $words === false ? [] : $words;

        $maxTokens = $options['max_tokens'] ?? null;

        if (\is_int($maxTokens)) {
            $words = \array_slice($words, 0, $maxTokens);
        }

        return implode(' ', $words);
    }
}
