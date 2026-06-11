# Cutting a release

Pure-PHP package: a release is a git tag plus Packagist metadata.
No binaries, no release workflow.

1. **Green CI on `main`** — cs, PHPStan, tests across the matrix.
2. **Update `PLAN.md`** — check off shipped items, note the version.
3. **Tag**, annotated, `v`-prefixed semver:

   ```sh
   git tag -a v0.1.0 -m "v0.1.0 — initial contract surface"
   git push origin v0.1.0
   ```

4. **Packagist** — first release only: submit
   `https://github.com/DisplaceTech/ai-contracts` at
   [packagist.org/packages/submit](https://packagist.org/packages/submit)
   under the `displace` vendor, then enable the GitHub hook so later
   tags publish automatically. Subsequent releases: pushing the tag is
   the whole job.
5. **Verify** — `composer require displace/ai-contracts:^0.1` in a
   scratch directory resolves and autoloads
   `Displace\AI\Contracts\Embedder`.

## Versioning policy

Interfaces are forever-contracts (see README "Versioning"). Within a
major: no method removals, no signature changes, no added methods.
Pre-1.0: minor versions may adjust the surface; patch versions are
docs/PHPDoc-only.
