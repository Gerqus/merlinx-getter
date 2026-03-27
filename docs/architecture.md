# merlinx-getter Architecture

## Overview

`skionline/merlinx-getter` is a standalone, read-only PHP library that acts as an Anti-Corruption Layer (ACL) facade over the MerlinX API. It absorbs MerlinX wire format quirks, pagination, rate limiting, caching, and error semantics so that consumer applications interact with a clean, stable interface.

### Scope Boundary

This package is a **data-access library** — it fetches and caches data from MerlinX. It does not do presentation, domain modeling, booking orchestration, or query normalization. Keep it that way. New functionality belongs here only if it is about *getting data from MerlinX* or *managing the transport and caching around it*.

---

## Principles

1. **Single entry point** — `MerlinxGetterClient` is the only public-facing class. Everything else is internal
2. **Anti-Corruption Layer** — MerlinX wire formats, error codes, and pagination never leak to consumers
3. **Constructor injection** — All dependencies (HTTP client, cache, logger) are injected. No service locator, no static state
4. **Operation per concern** — Each API interaction is encapsulated in an Operation class with its own caching, retry, and error handling
5. **Typed boundaries** — Inputs are typed request objects, outputs are typed result objects. No raw arrays at the public surface
6. **Immutable config** — All configuration flows through `MerlinxGetterConfig`, created once from a root array

---

## Blackbox Design

Every class and module in this package is treated as a **blackbox**. This is the single most important architectural constraint.

### What blackbox means

A blackbox has:
- A **public interface** — its constructor parameters and public methods. This is the only way to interact with it
- **Hidden internals** — private state, private methods, internal helpers. These can change freely without affecting anything outside

A blackbox does NOT:
- Expose internal state for others to interpret or act on
- Require callers to know how it works inside
- Reach into the internals of other blackboxes

### Applying blackbox thinking

**At the class level:**
- Every class has `private` as the default visibility. Only promote to `public` what consumers genuinely need
- Constructor parameters define the dependency contract. If a class needs something, it asks for it via constructor — it never goes looking through another object's internals
- Return types are the output contract. Return the minimal, self-contained result the caller needs — not raw internal data structures

**At the module level:**
- Each `src/` subdirectory is a module with one public-facing root service
- Subfolders (`Auxiliary/`, `Models/`) are private implementation details of that module
- Other modules interact only through the root service. Never import from another module's `Auxiliary/` or `Models/`

**At the package level:**
- `MerlinxGetterClient` is the single public surface. All classes behind it are implementation details
- Consumers depend on `MerlinxGetterClient`, typed request/result objects, and `MerlinxGetterException` subtypes — nothing else

### Why this matters

Blackbox boundaries create **change isolation**. When MerlinX changes their pagination model, only `SearchOperation` and its internal helpers change. When a cache strategy needs rework, only `Cache/*` changes. Nothing ripples outward because no one depends on those internals.

This is what makes the package maintainable under real-world conditions — where MerlinX API behavior changes without notice and multiple consumer applications depend on stability.

---

## Layered Architecture

The package has four layers with strict top-down dependency flow. Each layer has a clear role, and the layer below never knows about the layer above.

```
┌─────────────────────────────────────────────┐
│  Layer 1: Facade                            │
│  MerlinxGetterClient                        │
│  • single public entry point                │
│  • dependency wiring                        │
│  • delegates to operations                  │
├─────────────────────────────────────────────┤
│  Layer 2: Operations                        │
│  Operation/*                                │
│  • one class per API concern                │
│  • orchestrates: cache → http → transform   │
│  • owns pagination, retry, response merge   │
├─────────────────────────────────────────────┤
│  Layer 3: Domain Models                     │
│  Search/Execution/*, Search/Policy/*,       │
│  Search/Profile/*, Details/*,               │
│  Verification/*                             │
│  • typed request/result objects             │
│  • policies and business rules              │
│  • no I/O, no side effects                  │
├─────────────────────────────────────────────┤
│  Layer 4: Infrastructure                    │
│  Http/*, Cache/*, Config/*, Log/*,          │
│  Exception/*                                │
│  • authenticated transport                  │
│  • PSR-16 caching and distributed locking   │
│  • immutable configuration                  │
│  • structured error hierarchy               │
└─────────────────────────────────────────────┘
```

### Layer rules

| Rule | Rationale |
|------|-----------|
| **Facade** has no logic — only wiring and delegation | Keeps the public surface thin and stable |
| **Operations** compose lower layers but never modify them | New operations can be added without touching existing infrastructure |
| **Domain models** are pure data and logic — no I/O | They are unconditionally testable, portable, and reusable |
| **Infrastructure** knows nothing about operations or the facade | Prevents coupling to specific use cases; keeps utilities general-purpose |
| No upward dependencies — lower layers never import from upper layers | Guarantees that changes in upper layers never break lower ones |
| No lateral dependencies between operations | Each operation is self-contained; removing one cannot break another |

### Dependency direction

```
Facade → Operations → Domain Models
                    → Infrastructure

Domain Models → (nothing, or Infrastructure for utilities like ToObjectDeep)
Infrastructure → (nothing)
```

The key insight: **Operations** are the only layer that composes both Domain Models and Infrastructure. The facade just delegates. Domain models stay pure. Infrastructure stays general.

---

## Separation of Concerns

Each concern lives in exactly one place. If you find yourself scattering related logic across modules, that's a signal to centralize.

| Concern | Owner | Not here |
|---------|-------|----------|
| **Authentication** | `Http/AuthTokenProvider` | Not in operations, not in facade |
| **Rate-limit retry** | `Http/Auxiliary/RateLimitRetryEngine` | Not reimplemented per operation |
| **Response merging** | `Search/Util/TravelSearchResponseMerger` | Not in `SearchOperation` inline |
| **Cache namespace management** | `Cache/NamespacedCache` | Not hand-rolled per operation |
| **Distributed locking** | `Cache/FileKeyLock` | Not ad-hoc per operation |
| **Error mapping/wrapping** | `Exception/*` + `Http/Auxiliary/HttpErrorReporter` | Not raw catches scattered everywhere |
| **Config access** | `Config/MerlinxGetterConfig` | Not parsed from arrays in operations |

### How concerns compose in an operation

An operation class is a **thin orchestrator** that sequences calls to concern owners:

```
1. Read config               → MerlinxGetterConfig (injected)
2. Check cache                → NamespacedCache (injected)
3. Acquire lock               → FileKeyLock (injected)
4. Send HTTP request          → MerlinxHttpClient (injected, handles auth + retry internally)
5. Transform response         → Search/Util/* (pure functions)
6. Store in cache             → NamespacedCache
7. Wrap result                → typed result object
```

The operation itself owns only the **sequence** and **decisions** (when to paginate, when to stop, when to serve stale cache). The *how* of each step is somebody else's responsibility.

---

## Module Encapsulation

Each `src/` subdirectory is a module. Follow the convention:

```
src/SomeModule/
├── SomeModuleService.php     ← the only file other modules may import
├── Auxiliary/
│   └── InternalHelper.php    ← private implementation detail
└── Models/
    └── InternalDto.php       ← private data structure
```

- **One system-facing service** at the module root
- **Internal helpers** in `Auxiliary/`
- **Data models** in `Models/`
- Do not import from another module's `Auxiliary/` or `Models/` — interact through the root service
- If a utility is needed by multiple modules, promote it to its own module with its own root service — don't create back-channel imports

---

## Adding a New Operation

1. Create a class in `src/Operation/` implementing `OperationInterface`
2. Accept `MerlinxGetterConfig`, `MerlinxHttpClient`, and `NamespacedCache` via constructor
3. Own the orchestration: cache check → HTTP call(s) → response assembly → cache store
4. Add a public method on `MerlinxGetterClient` that delegates to the new operation
5. Add the cache namespace to `MerlinxGetterClient::clearCache()`
6. Wrap raw HTTP/transport exceptions in `MerlinxGetterException` subtypes — never let them escape
7. Test with a mocked `MockHttpClient` — no real API calls

### Where to put new code

| What you're adding | Where it goes |
|---------------------|---------------|
| New MerlinX endpoint integration | `src/Operation/` (new operation class) |
| Request/result types for the operation | `src/Search/Execution/` or a new sibling module under `src/` if unrelated to search |
| Response transformation utility | `src/Search/Util/` or a new `Util/` under the relevant module |
| Policy or business rule | `src/Search/Policy/` or a new `Policy/` under the relevant module |
| Reusable HTTP concern (retry, auth) | `src/Http/Auxiliary/` |
| New exception type | `src/Exception/` — must extend `MerlinxGetterException` |
| New config keys | `src/Config/MerlinxGetterConfig.php` — provide defaults |

---

## Error Handling

All exceptions extend `MerlinxGetterException` (`RuntimeException`).

**Rules:**
- Catch raw HTTP/transport exceptions at the operation or Http layer and wrap them in package exception types
- Rate-limit (HTTP 429) errors are retried automatically before surfacing
- Never throw generic `\Exception` or `\RuntimeException` — always use a typed subclass
- When adding a new exception type, place it in `src/Exception/` and extend `MerlinxGetterException`
- Exceptions are part of the public contract — consumers catch them. Their class names and message semantics must be stable

---

## Caching Conventions

- Each operation owns its cache namespace, versioned (e.g., `merlinx_getter.search.v2`)
- **Bump the version suffix** when the cache value format changes — this silently invalidates old entries
- Use `FileKeyLock` for distributed lock around expensive cache refreshes to prevent thundering herd
- Support stale-cache fallback: if a fresh fetch fails within lock timeout, serve stale data rather than error
- Consumers can inject their own PSR-16 cache; the default is filesystem-based
- Cache logic stays inside operations — the facade and domain models don't know about caching

---

## Stability and Change

Not all parts of the package change at the same rate. Understanding this helps decide where to invest in stability.

| Component | Change frequency | Stability investment |
|-----------|-----------------|---------------------|
| `MerlinxGetterClient` public API | Rarely — this is the consumer contract | High. Breaking changes require major version bumps |
| Typed request/result objects | Rarely — these are the contract too | High. Additive changes only |
| `MerlinxGetterException` subtypes | Rarely | High. Consumers catch these |
| Operations | When MerlinX API changes or new endpoints are added | Medium. Well-tested but expect internal churn |
| Search utilities and policies | When MerlinX response formats or business rules change | Medium. Pure functions are easy to test and adjust |
| Http internals and retry logic | When MerlinX changes rate-limit behavior | Low stability needed — hidden behind `MerlinxHttpClient` blackbox |
| Cache internals | When performance tuning or infrastructure changes | Low stability needed — hidden behind `NamespacedCache` |

**Rule of thumb:** The further from the public surface, the more freely you can change. Blackbox encapsulation guarantees this.

---

## Design Decisions for Robustness

### Prefer injectable collaborators over hardcoded creation

Every dependency that touches I/O or has behavior worth testing should be injected via constructor. The default pattern:

```php
public function __construct(
    private readonly MerlinxGetterConfig $config,
    private readonly MerlinxHttpClient $client,
    ?CacheInterface $cache = null,
) {
    $this->cache = $cache ?? (new FilesystemCacheFactory($config->cacheDir))->create('namespace');
}
```

Optional parameters with sensible defaults — production wiring works out of the box, tests inject mocks.

### Prefer composition over conditional paths

When behavior varies (e.g., different API endpoints, different cache strategies), create a new operation class rather than branching inside an existing one. Two small focused classes are better than one class with mode flags.

### Prefer pure transformations

Response merging, field pruning, request fingerprinting — these are pure functions that take data in and return data out. Keep them in `Util/` or `Policy/` directories with no I/O, no injected dependencies. They are trivially testable and reusable.

### Prefer fail-loud on wiring, fail-soft on runtime

- **Config errors** → throw immediately at construction time. Don't let misconfigured objects enter the system
- **HTTP failures** → retry, fall back to stale cache, then throw a structured exception. Give the consumer a chance to degrade gracefully
- **Response format surprises** → throw `ResponseFormatException` with context. Don't silently swallow unexpected shapes

---

## Testing Practices

### Test the blackbox, not the internals

Tests should exercise the **public interface** and verify **observable outcomes**. Never test private methods, internal state, or implementation sequences.

**Right approach:** instantiate `MerlinxGetterClient` with a mock HTTP client, call `executeSearch()`, assert on the returned `SearchExecutionResult`.

**Wrong approach:** directly instantiate `TravelSearchResponseMerger`, feed it hand-crafted intermediate structures, and assert on merge internals.

The exception is utility classes in `Util/` and `Policy/` — these *are* the public interface of the Domain Models layer and have their own unit tests.

### Mock at the boundary, not at every seam

The only mock needed for most tests is `MockHttpClient` with `MockResponse` sequences. This validates the entire stack from facade through operations through HTTP — exactly as production runs, minus the network.

Don't mock `NamespacedCache`, `FileKeyLock`, or `AuthTokenProvider` unless testing their specific behavior in isolation. Over-mocking creates tests that pass even when the real wiring is broken.

### Error contract tests are mandatory

Every new operation must have tests verifying that specific HTTP status codes (429, 500, 502, timeout) produce the correct `MerlinxGetterException` subtype. These are the tests that save you at 2 AM.

### Tests use `tests/run_all.sh` with shared bootstrap in `tests/bootstrap.php`

---

## Consumer Integration

Consumers install via Composer and interact only with `MerlinxGetterClient`. MerlinX wire formats never escape the package boundary.
