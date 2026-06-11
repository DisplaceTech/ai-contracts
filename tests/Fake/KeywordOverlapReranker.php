<?php

declare(strict_types=1);

namespace Displace\AI\Contracts\Tests\Fake;

use Displace\AI\Contracts\Reranker;

/**
 * Scores each document by how many distinct query words it contains.
 * Crude, but enough relevance signal to exercise ordering, index
 * mapping, and topK semantics.
 */
final class KeywordOverlapReranker implements Reranker
{
    public function rerank(string $query, array $documents, ?int $topK = null): array
    {
        $queryWords = self::words($query);
        $rows = [];

        foreach ($documents as $index => $document) {
            $overlap = \count(array_intersect($queryWords, self::words($document)));
            $rows[] = ['index' => $index, 'score' => (float) $overlap];
        }

        // Best-first; ties broken by input order to stay deterministic.
        usort($rows, static fn(array $a, array $b): int => [$b['score'], $a['index']] <=> [$a['score'], $b['index']]);

        return $topK === null ? $rows : \array_slice($rows, 0, $topK);
    }

    /**
     * @return list<string>
     */
    private static function words(string $text): array
    {
        $words = preg_split('/\W+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY);

        return $words === false ? [] : array_values(array_unique($words));
    }
}
