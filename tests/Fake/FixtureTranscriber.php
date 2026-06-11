<?php

declare(strict_types=1);

namespace Displace\AI\Contracts\Tests\Fake;

use Displace\AI\Contracts\Transcriber;

/**
 * Replays canned segments for any existing file path — exercises the
 * return-shape contract without any audio machinery.
 */
final class FixtureTranscriber implements Transcriber
{
    /**
     * @param list<array{start: float, end: float, text: string}> $segments
     */
    public function __construct(private readonly array $segments) {}

    public function transcribe(string $audioPath, array $options = []): array
    {
        if (!is_file($audioPath)) {
            throw new \InvalidArgumentException(\sprintf('No such audio file: %s', $audioPath));
        }

        return [
            'text' => implode(' ', array_column($this->segments, 'text')),
            'segments' => $this->segments,
        ];
    }
}
