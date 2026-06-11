<?php

declare(strict_types=1);

namespace Displace\AI\Contracts\Tests;

use Displace\AI\Contracts\Tests\Fake\InMemoryVectorIndex;
use PHPUnit\Framework\TestCase;

final class VectorIndexContractTest extends TestCase
{
    private const DIM = 4;

    /**
     * @param list<float> $floats
     */
    private static function vec(array $floats): string
    {
        return pack('g*', ...$floats);
    }

    private static function indexWithCorpus(): InMemoryVectorIndex
    {
        $index = new InMemoryVectorIndex(self::DIM);
        $index->add(
            self::vec([1.0, 0.0, 0.0, 0.0])
            . self::vec([0.0, 1.0, 0.0, 0.0])
            . self::vec([0.9, 0.1, 0.0, 0.0])
            . self::vec([0.0, 0.0, 1.0, 0.0]),
            [10, 20, 30, 40],
        );

        return $index;
    }

    public function testSearchReturnsBestFirstRows(): void
    {
        $results = self::indexWithCorpus()->search(self::vec([1.0, 0.0, 0.0, 0.0]), k: 2);

        self::assertCount(2, $results);
        self::assertSame(10, $results[0]['id']);
        self::assertSame(30, $results[1]['id']);
        self::assertGreaterThan($results[1]['score'], $results[0]['score']);
    }

    public function testSearchReturnsFewerRowsThanKWhenIndexIsSmall(): void
    {
        $results = self::indexWithCorpus()->search(self::vec([1.0, 0.0, 0.0, 0.0]), k: 100);

        self::assertCount(4, $results);
    }

    public function testAllowlistRestrictsResultsToItsIds(): void
    {
        $results = self::indexWithCorpus()->search(
            self::vec([1.0, 0.0, 0.0, 0.0]),
            k: 10,
            allowlist: [20, 40],
        );

        self::assertSame([20, 40], array_column($results, 'id'));
    }

    public function testEmptyAllowlistIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        self::indexWithCorpus()->search(self::vec([1.0, 0.0, 0.0, 0.0]), allowlist: []);
    }

    public function testCountTracksAddAndRemove(): void
    {
        $index = self::indexWithCorpus();

        self::assertCount(4, $index);

        $index->remove(20);

        self::assertCount(3, $index);
    }

    public function testRemovingAnUnknownIdThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        self::indexWithCorpus()->remove(999);
    }

    public function testRemovedIdsDoNotAppearInResults(): void
    {
        $index = self::indexWithCorpus();
        $index->remove(10);

        $ids = array_column($index->search(self::vec([1.0, 0.0, 0.0, 0.0]), k: 10), 'id');

        self::assertNotContains(10, $ids);
    }

    public function testIdCountMismatchRejectsTheWholeBatch(): void
    {
        $index = new InMemoryVectorIndex(self::DIM);

        try {
            $index->add(self::vec([1.0, 0.0, 0.0, 0.0]) . self::vec([0.0, 1.0, 0.0, 0.0]), [1]);
            self::fail('Expected InvalidArgumentException.');
        } catch (\InvalidArgumentException) {
        }

        self::assertCount(0, $index);
    }

    public function testDuplicateIdRejectsTheWholeBatch(): void
    {
        $index = self::indexWithCorpus();

        try {
            // Id 99 is new, id 10 already exists — neither may land.
            $index->add(self::vec([0.5, 0.5, 0.0, 0.0]) . self::vec([0.0, 0.5, 0.5, 0.0]), [99, 10]);
            self::fail('Expected InvalidArgumentException.');
        } catch (\InvalidArgumentException) {
        }

        self::assertCount(4, $index);
    }

    public function testRaggedPayloadIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new InMemoryVectorIndex(self::DIM))->add("\x00\x00\x80\x3f", [1]); // one float, not one vector
    }

    public function testQueryMustBeExactlyOneVector(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        self::indexWithCorpus()->search(self::vec([1.0, 0.0]));
    }
}
