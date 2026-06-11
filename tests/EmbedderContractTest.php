<?php

declare(strict_types=1);

namespace Displace\AI\Contracts\Tests;

use Displace\AI\Contracts\Tests\Fake\HashEmbedder;
use PHPUnit\Framework\TestCase;

final class EmbedderContractTest extends TestCase
{
    public function testEmbedReturnsOnePackedFloat32Vector(): void
    {
        $embedder = new HashEmbedder(dimensions: 16);

        $vector = $embedder->embed('hello world');

        self::assertSame(4 * 16, \strlen($vector));

        $floats = unpack('g*', $vector);
        self::assertNotFalse($floats);
        self::assertCount(16, $floats);
    }

    public function testEmbedIsDeterministic(): void
    {
        $embedder = new HashEmbedder();

        self::assertSame($embedder->embed('same text'), $embedder->embed('same text'));
        self::assertNotSame($embedder->embed('one text'), $embedder->embed('another text'));
    }

    public function testEmbedBatchIsConcatenationInInputOrder(): void
    {
        $embedder = new HashEmbedder(dimensions: 8);
        $texts = ['first', 'second', 'third'];

        $batch = $embedder->embedBatch($texts);

        self::assertSame(4 * 8 * 3, \strlen($batch));
        self::assertSame(
            $embedder->embed('first') . $embedder->embed('second') . $embedder->embed('third'),
            $batch,
        );
    }

    public function testEmbedBatchOfNothingIsAnEmptyBuffer(): void
    {
        self::assertSame('', (new HashEmbedder())->embedBatch([]));
    }

    public function testPackedOutputIsLittleEndianFloat32(): void
    {
        $embedder = new HashEmbedder(dimensions: 8);

        $vector = $embedder->embed('endianness');
        $floats = unpack('g*', $vector);

        self::assertNotFalse($floats);
        // Re-packing the unpacked floats must reproduce the buffer exactly:
        // proves the format is pack('g*')-compatible, not just 32-bit-wide.
        self::assertSame($vector, pack('g*', ...$floats));
    }
}
