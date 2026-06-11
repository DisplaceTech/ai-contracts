<?php

declare(strict_types=1);

namespace Displace\AI\Contracts;

/**
 * A similarity-searchable collection of vectors addressed by stable,
 * caller-chosen integer ids (SQL primary keys, post IDs, ...).
 *
 * Vectors enter as **packed little-endian float32 binary strings** — the
 * output of `pack('g*', ...$floats)`, batched by plain concatenation. This
 * is the only accepted vector representation: one code path, no silent
 * slow path through PHP arrays. {@see Embedder} implementations produce
 * exactly this format.
 *
 * The contract is deliberately silent on durability and process topology.
 * An implementation may be in-memory, file-backed, or a database driver;
 * read-mostly snapshot patterns (build offline, load read-only) are an
 * implementation concern.
 */
interface VectorIndex extends \Countable
{
    /**
     * Add a batch of vectors with caller-chosen stable ids — one
     * non-negative integer per vector, in payload order.
     *
     * Implementations MUST reject the whole batch (id count mismatch,
     * duplicate or already-present id, malformed payload) rather than
     * partially apply it.
     *
     * @param string    $vectors Concatenated packed float32 vectors;
     *                           `strlen()` must be a whole multiple of
     *                           `4 * dim`.
     * @param list<int> $ids     One id per vector in the payload.
     */
    public function add(string $vectors, array $ids): void;

    /**
     * Top-`k` similarity search for a single packed query vector,
     * optionally restricted to `$allowlist` ids.
     *
     * Results are ordered best-first; higher score means more similar.
     * Fewer than `$k` rows are returned when the index (or the allowlist)
     * holds fewer vectors. With an allowlist, every returned id is from
     * the allowlist — the canonical recipe is a SQL pre-filter
     * (`SELECT id FROM ... WHERE tenant = ?`) feeding the allowlist.
     *
     * @param string         $query     Exactly one packed float32 vector.
     * @param int            $k         Maximum rows to return; at least 1.
     * @param list<int>|null $allowlist Restrict results to these ids;
     *                                  `null` searches the whole index.
     *
     * @return list<array{id: int, score: float}> Best-first result rows.
     */
    public function search(string $query, int $k = 10, ?array $allowlist = null): array;

    /**
     * Remove the vector with this id. Implementations SHOULD throw when
     * the id is not present — a remove that removes nothing is almost
     * always a caller bug.
     */
    public function remove(int $id): void;

    /**
     * Number of vectors currently in the index.
     */
    public function count(): int;
}
