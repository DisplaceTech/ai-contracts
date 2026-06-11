<?php

declare(strict_types=1);

namespace Displace\AI\Contracts\Tests\Fake;

use Displace\AI\Contracts\VectorIndex;

/**
 * Brute-force, exact inner-product reference implementation of the
 * VectorIndex contract. O(n) per search — a conformance oracle, not a
 * production index.
 */
final class InMemoryVectorIndex implements VectorIndex
{
    /** @var array<int, list<float>> */
    private array $vectors = [];

    public function __construct(private readonly int $dimensions) {}

    public function add(string $vectors, array $ids): void
    {
        $bytesPerVector = 4 * $this->dimensions;

        if (\strlen($vectors) % $bytesPerVector !== 0) {
            throw new \InvalidArgumentException(\sprintf(
                'Payload of %d bytes is not a whole number of %d-dimensional float32 vectors.',
                \strlen($vectors),
                $this->dimensions,
            ));
        }

        $count = intdiv(\strlen($vectors), $bytesPerVector);

        if ($count !== \count($ids)) {
            throw new \InvalidArgumentException(\sprintf(
                'Payload holds %d vectors but %d ids were given.',
                $count,
                \count($ids),
            ));
        }

        // Stage the whole batch first: a failed add() never partially applies.
        $staged = [];

        foreach ($ids as $i => $id) {
            if ($id < 0) {
                throw new \InvalidArgumentException(\sprintf('Negative id %d.', $id));
            }

            if (isset($this->vectors[$id]) || isset($staged[$id])) {
                throw new \InvalidArgumentException(\sprintf('Duplicate id %d.', $id));
            }

            $staged[$id] = self::unpackOne(substr($vectors, $i * $bytesPerVector, $bytesPerVector));
        }

        $this->vectors += $staged;
    }

    public function search(string $query, int $k = 10, ?array $allowlist = null): array
    {
        if ($k < 1) {
            throw new \InvalidArgumentException('k must be at least 1.');
        }

        if (\strlen($query) !== 4 * $this->dimensions) {
            throw new \InvalidArgumentException('Query must be exactly one packed vector.');
        }

        if ($allowlist !== null) {
            if ($allowlist === []) {
                throw new \InvalidArgumentException('Allowlist must be non-empty; pass null to search unfiltered.');
            }

            foreach ($allowlist as $id) {
                if (!isset($this->vectors[$id])) {
                    throw new \InvalidArgumentException(\sprintf('Allowlist id %d is not in the index.', $id));
                }
            }
        }

        $q = self::unpackOne($query);
        $candidates = $allowlist === null
            ? $this->vectors
            : array_intersect_key($this->vectors, array_flip(array_unique($allowlist)));

        $rows = [];

        foreach ($candidates as $id => $vector) {
            $score = 0.0;

            foreach ($vector as $i => $coordinate) {
                $score += $coordinate * $q[$i];
            }

            $rows[] = ['id' => $id, 'score' => $score];
        }

        usort($rows, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);

        return \array_slice($rows, 0, $k);
    }

    public function remove(int $id): void
    {
        if (!isset($this->vectors[$id])) {
            throw new \InvalidArgumentException(\sprintf('Id %d is not in the index.', $id));
        }

        unset($this->vectors[$id]);
    }

    public function count(): int
    {
        return \count($this->vectors);
    }

    /**
     * @return list<float>
     */
    private static function unpackOne(string $packed): array
    {
        $raw = unpack('g*', $packed);

        if ($raw === false) {
            throw new \RuntimeException('unpack() failed.');
        }

        $floats = [];

        foreach ($raw as $value) {
            if (!\is_float($value)) {
                throw new \RuntimeException('unpack() returned a non-float value.');
            }

            $floats[] = $value;
        }

        return $floats;
    }
}
