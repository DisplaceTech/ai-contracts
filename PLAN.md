# ai-contracts — design notes

Part of the [phpai stack](https://github.com/DisplaceTech). Thin by
design: this package is the framework-integration surface for the
Displace local-first AI primitives. Strategy: publish interfaces, let
existing frameworks (LLPhant, NeuronAI, Prism, ...) ship the drivers.
We do not build an orchestration framework.

This file holds design notes and decision history, not a to-do list.
What shipped is in [`CHANGELOG.md`](CHANGELOG.md); remaining ideas are
[`future` issues](https://github.com/DisplaceTech/ai-contracts/issues?q=is%3Aissue+is%3Aopen+label%3Afuture), each with its reasoning.

## v0.1.0 scope (shipped 2026-06-11)

- [x] `Embedder` — packed float32 out; batch = concatenation.
- [x] `VectorIndex` — stable int ids, allowlist search, `Countable`.
- [x] `Generator` — prompt + options array, lowest common denominator.
- [x] `Reranker` — best-first `{index, score}` rows.
- [x] `Transcriber` — text + timestamped segments shape.
- [x] Conformance tests with in-memory reference fakes.
- [x] CI (cs + PHPStan max + PHPUnit, PHP 8.3/8.4/8.5 × Linux/macOS).
- [x] Tag v0.1.0, register on Packagist (see RELEASE.md) — shipped 2026-06-11.
- [x] Rewrite ext-infer / ext-turbovec doc examples against the
      interfaces where it clarifies — done 2026-06-11 (recipes in
      both repos).

## Remaining work

Tracked as [`future` issues](https://github.com/DisplaceTech/ai-contracts/issues?q=is%3Aissue+is%3Aopen+label%3Afuture):

- [#1](https://github.com/DisplaceTech/ai-contracts/issues/1) Framework drivers against the contracts (first-party adapter package only as a fallback)
- [#2](https://github.com/DisplaceTech/ai-contracts/issues/2) Exception-contract interfaces, if implementors ask
- [#3](https://github.com/DisplaceTech/ai-contracts/issues/3) Streaming `Generator` variant, no earlier than ext-infer v0.3 streaming

## Design notes

- **Why packed strings?** See ARCHITECTURE.md in the umbrella repo: PHP
  arrays inflate each coordinate to a zval; packed buffers cross FFI
  boundaries contiguously. One code path, no silent slow path.
- **Why arrays, not result objects, for search/rerank/transcribe
  rows?** A contracts package exporting concrete classes forces them
  on every implementor and consumer; shaped arrays (documented with
  array-shape PHPDoc, enforced by PHPStan) keep the package
  implementation-free.
- **Why no `Chunker` contract?** Chunking is a pure-PHP utility, not a
  swappable engine — it lives in `displace/ai-toolkit` as classes.
