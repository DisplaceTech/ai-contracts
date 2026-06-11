<?php

declare(strict_types=1);

namespace Displace\AI\Contracts\Tests;

use Displace\AI\Contracts\Tests\Fake\FixtureTranscriber;
use PHPUnit\Framework\TestCase;

final class TranscriberContractTest extends TestCase
{
    private const SEGMENTS = [
        ['start' => 0.0, 'end' => 2.5, 'text' => 'Good morning everyone.'],
        ['start' => 2.5, 'end' => 5.0, 'text' => 'Today we talk about PHP.'],
    ];

    public function testReturnsTextAndTimeOrderedSegments(): void
    {
        $result = (new FixtureTranscriber(self::SEGMENTS))->transcribe(__FILE__);

        self::assertSame('Good morning everyone. Today we talk about PHP.', $result['text']);
        self::assertCount(2, $result['segments']);

        $previousEnd = 0.0;

        foreach ($result['segments'] as $segment) {
            self::assertGreaterThanOrEqual($previousEnd, $segment['start']);
            self::assertGreaterThan($segment['start'], $segment['end']);
            $previousEnd = $segment['end'];
        }
    }

    public function testSegmentsConcatenateToTheFullTranscript(): void
    {
        $result = (new FixtureTranscriber(self::SEGMENTS))->transcribe(__FILE__);

        self::assertSame(
            $result['text'],
            implode(' ', array_column($result['segments'], 'text')),
        );
    }

    public function testMissingFileThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new FixtureTranscriber(self::SEGMENTS))->transcribe('/no/such/file.wav');
    }

    public function testUnknownOptionKeysAreSilentlyIgnored(): void
    {
        $result = (new FixtureTranscriber(self::SEGMENTS))->transcribe(__FILE__, ['made_up_key' => 42]);

        self::assertNotSame('', $result['text']);
    }
}
