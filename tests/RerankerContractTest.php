<?php

declare(strict_types=1);

namespace Displace\AI\Contracts\Tests;

use Displace\AI\Contracts\Tests\Fake\KeywordOverlapReranker;
use PHPUnit\Framework\TestCase;

final class RerankerContractTest extends TestCase
{
    private const DOCUMENTS = [
        'the weather is sunny today',          // index 0 — 0 query words
        'php runs models in process',          // index 1 — 1 query word
        'vector search with php extensions',   // index 2 — 3 query words
    ];

    public function testRowsComeBackBestFirstWithInputIndexes(): void
    {
        $rows = (new KeywordOverlapReranker())->rerank('php vector search', self::DOCUMENTS);

        self::assertCount(3, $rows);
        self::assertSame([2, 1, 0], array_column($rows, 'index'));

        $scores = array_column($rows, 'score');
        $sorted = $scores;
        rsort($sorted);
        self::assertSame($sorted, $scores);
    }

    public function testTopKKeepsOnlyTheBestRows(): void
    {
        $rows = (new KeywordOverlapReranker())->rerank('php vector search', self::DOCUMENTS, topK: 1);

        self::assertCount(1, $rows);
        self::assertSame(2, $rows[0]['index']);
    }

    public function testNullTopKScoresEveryDocument(): void
    {
        $rows = (new KeywordOverlapReranker())->rerank('anything', self::DOCUMENTS, topK: null);

        self::assertCount(\count(self::DOCUMENTS), $rows);
    }

    public function testEmptyCandidateListYieldsNoRows(): void
    {
        self::assertSame([], (new KeywordOverlapReranker())->rerank('query', []));
    }
}
