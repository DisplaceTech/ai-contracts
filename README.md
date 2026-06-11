<h1 align="center">ai-contracts</h1>

<p align="center">
  <strong>Stable PHP interfaces for local-first AI primitives.</strong><br>
  Embeddings, generation, vector search, reranking, transcription — zero dependencies, zero implementations.
</p>

<p align="center">
  <a href="https://github.com/DisplaceTech/ai-contracts/actions/workflows/ci.yml"><img src="https://github.com/DisplaceTech/ai-contracts/actions/workflows/ci.yml/badge.svg" alt="CI" /></a>
  <a href="https://packagist.org/packages/displace/ai-contracts"><img src="https://img.shields.io/packagist/v/displace/ai-contracts" alt="Packagist" /></a>
  <img src="https://img.shields.io/badge/PHP-8.3%20%7C%208.4%20%7C%208.5-777BB4?logo=php&logoColor=white" alt="PHP 8.3 / 8.4 / 8.5" />
  <img src="https://img.shields.io/badge/dependencies-none-brightgreen" alt="Zero dependencies" />
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-green" alt="MIT License" /></a>
</p>

---

## What is ai-contracts?

Five interfaces under `Displace\AI\Contracts` that describe what AI
primitives *do*, without saying how:

| Interface | Contract |
|---|---|
| `Embedder` | text in → packed float32 vector out |
| `VectorIndex` | id-addressed similarity search with allowlist filtering |
| `Generator` | prompt in → completion out |
| `Reranker` | query + candidates → best-first relevance scores |
| `Transcriber` | audio file in → transcript + timestamped segments |

The package contains **no implementations and no dependencies** — it is
the integration surface between things that provide AI primitives
([ext-infer](https://github.com/DisplaceTech/ext-infer),
[ext-turbovec](https://github.com/DisplaceTech/ext-turbovec), hosted
APIs, anything else) and things that consume them (LLPhant, NeuronAI,
Prism, your application). Frameworks ship the drivers; applications code
against the interfaces; providers can be swapped without touching either.

```sh
composer require displace/ai-contracts
```

## The packed-vector contract

Every vector crossing these interfaces is a **packed little-endian
float32 binary string** — the output of `pack('g*', ...$floats)` — never
a PHP float array. Arrays inflate every coordinate into a zval; packed
strings are a single contiguous buffer that moves through FFI and
extension boundaries with zero per-element overhead, and batches compose
by plain string concatenation:

```php
use Displace\AI\Contracts\Embedder;
use Displace\AI\Contracts\VectorIndex;

function indexPosts(Embedder $embedder, VectorIndex $index, array $posts): void
{
    // One packed buffer for the whole batch, one add() call.
    $index->add(
        $embedder->embedBatch(array_column($posts, 'content')),
        array_column($posts, 'id'),
    );
}

function searchPosts(Embedder $embedder, VectorIndex $index, string $query, array $visibleIds): array
{
    // The allowlist composes with a SQL pre-filter:
    //   SELECT id FROM posts WHERE status = 'publish'  →  $visibleIds
    return $index->search($embedder->embed($query), k: 10, allowlist: $visibleIds);
}
```

Nothing above names a concrete engine. Wire in a llama.cpp-backed
embedder and an in-process quantized index today, swap either side
tomorrow.

## Writing an adapter

Implementations are intentionally easy to write — here is a complete
`Embedder` over ext-infer:

```php
use Displace\AI\Contracts\Embedder;
use Displace\Infer\Model;

final class InferEmbedder implements Embedder
{
    public function __construct(private readonly Model $model) {}

    public function embed(string $text): string
    {
        return pack('g*', ...$this->model->embed($text)->vector());
    }

    public function embedBatch(array $texts): string
    {
        return implode('', array_map($this->embed(...), $texts));
    }

    public function dimensions(): int
    {
        return $this->model->embed('')->dimensions();
    }
}
```

The test suite ships in-memory reference fakes
([`tests/Fake/`](tests/Fake/)) that double as executable documentation
of each contract's semantics — the `InMemoryVectorIndex` is a
brute-force oracle you can test your own adapter against.

## Versioning

Interfaces are forever-contracts: methods are never removed or
re-signatured within a major version, and new methods only arrive with
a major bump (an interface addition is a BC break for every
implementor). Pre-1.0, minor versions may still adjust the surface —
pin accordingly.

## Deliberately out of scope

**Implementations** (this package never gains a class) · **an
orchestration framework** — chains, agents, pipelines, prompt templates
belong to the frameworks integrating these contracts · **chat-message
abstractions** — every framework already has one; `Generator` is the
lowest common denominator they adapt down to · **streaming
interfaces** — premature until the underlying local engines ship
streaming · **training / fine-tuning**.

## License

[MIT](LICENSE) &copy; 2026 Eric Mann / Displace Technologies
