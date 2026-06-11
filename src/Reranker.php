<?php

declare(strict_types=1);

namespace Displace\AI\Contracts;

/**
 * Second-stage relevance scoring for retrieval pipelines.
 *
 * A reranker sees the query and each candidate document *together*
 * (cross-encoding, or an equivalent scoring scheme) and is therefore more
 * accurate — and much more expensive — than embedding similarity. The
 * canonical pipeline is recall with {@see Embedder} + {@see VectorIndex},
 * then precision over the short candidate list with a reranker.
 */
interface Reranker
{
    /**
     * Score `$documents` against `$query`.
     *
     * Returns one row per kept document, ordered best-first. `index` is
     * the document's position in the input list — callers map it back to
     * their own ids or payloads. Higher score means more relevant; the
     * score scale is implementation-defined (only the ordering is
     * portable).
     *
     * @param list<string> $documents Candidate texts, typically the top-k
     *                                of a vector search.
     * @param int|null     $topK      Keep only the best `$topK` rows;
     *                                `null` returns all documents scored.
     *
     * @return list<array{index: int, score: float}> Best-first rows.
     */
    public function rerank(string $query, array $documents, ?int $topK = null): array;
}
