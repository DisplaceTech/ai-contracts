# Changelog

All notable changes to ai-contracts are documented here. The format is
based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and
this project adheres to [Semantic Versioning](https://semver.org/).
Interfaces are forever-contracts within a major; pre-1.0, a minor may
still adjust the surface. Dates are tag dates.

## [0.1.1] - Unreleased

No interface changes.

### Changed

- CI runs weekly as well as on push, and tests PHP 8.5 on macOS arm64
  again; setup-php is unpinned from 2.36.0, which carried known
  advisories. Dependabot keeps the dev toolchain and actions current.
- README: Status section, stack table, links to the write-up; PLAN.md
  reframed as design notes; remaining ideas moved to `future` issues.

## [0.1.0] - 2026-06-11

First release.

### Added

- `Embedder` (packed float32 out; `embedBatch()` concatenates),
  `VectorIndex` (stable int ids, allowlist-filtered top-k, `Countable`),
  `Generator`, `Reranker` (best-first `{index, score}` rows) and
  `Transcriber` (text + timestamped segments).
- Conformance tests with in-memory reference fakes;
  `InMemoryVectorIndex` is a brute-force oracle for adapter tests.

[0.1.1]: https://github.com/DisplaceTech/ai-contracts/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/DisplaceTech/ai-contracts/releases/tag/v0.1.0
