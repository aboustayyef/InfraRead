# Objectives

Refactor the existing monolithic Laravel RSS reader into a cleanly separated system:

1. A backend service exposing a stable, versioned, token‑protected JSON API that ingests, processes, organizes, enriches (summaries, plugins) and stores feed data.
2. One (later multiple) independent frontend clients (initially a Vue SPA in a separate repository) that consume only the public API for all read & write operations, preserving existing UX (keyboard shortcuts, fast navigation).
3. Extensibility for additional platform clients (mobile / desktop) without further backend coupling.

---

## Current Structure (Baseline Before Refactor)

Single Laravel app handling:
- Scheduled/Cron RSS fetch & plugin processing (store normalized posts, media, categories, sources).
- Server-rendered & Vue-enhanced web UI (reading, mark read/unread, save for later, subscribe to feeds, AI summarize, etc.) using bespoke endpoints tightly coupled to views.
- Plugin system for post‑processing (e.g., fixing relative links, legibility, marking read, text transformations).

Constraints & Preferences:
- No Docker for local dev (run natively on host macOS; keep setup simple: PHP + Composer + Node). 
- Prefer integrating with an existing 3rd‑party "read it later" ecosystem (e.g., Pocket / Readwise / Instapaper) rather than building a heavy proprietary read‑later silo. Internal flagging can exist but external export/integration is the strategic direction.
- Maintain plugin architecture; future plugins should be API‑triggerable where meaningful.
- Favor incremental, test‑backed extraction over big bang rewrite.

---

## Progress So Far (Phase 1 Complete)

Backend API Foundations (Read / Derived Data):
- Introduced versioned API namespace (`/api/v1`).
- Implemented authenticated read‑only endpoints for posts, sources, categories (filtering / includes retained where applicable).
- Added on‑demand AI summary endpoint (`POST /api/v1/posts/{post}/summary`) with rate limiting.
- Integrated Laravel Sanctum personal access tokens; all v1 endpoints protected by `auth:sanctum`.
- Implemented rate limiter group for summary generation.

Auth & Developer Experience:
- Built minimal in‑app API token management page (generate & revoke tokens) under admin UI
- Added temporary API Tester Blade page allowing manual requests with Bearer token injection (facilitates manual QA before external client exists).

Documentation & Housekeeping:
- Updated `README` with Phase 1 API usage, auth instructions, and summary endpoint details.
- Milestone commit created capturing stable Phase 1 baseline.
- Refactored views to unify admin layout; cleaned up CSS linking issues.

Testing & Reliability:
- Feature tests cover authentication enforcement, filtering, summary success/failure, and rate limiting boundaries (baseline set—will expand in later phases).

Architecture Insights Captured:
- Tailwind custom screen config currently only defines `md` (600px); design choices adjusted accordingly.
- `User` model updated (`HasApiTokens`) enabling token issuance with fillable adjustments.

---

## High-Level Architecture Target

Layers / Responsibilities:
1. Ingestion Layer: Scheduled fetchers + queue jobs fetch feeds, dedupe, persist.
2. Processing Pipeline: Pluggable transformations (link fixes, readability, summarization, tagging) executed synchronously or queued.
3. Domain Models & Persistence: Clear boundaries; API resources transform models to client DTOs.
4. API Layer: Versioned (`/api/v1` -> future `/api/v2`), token scopes, consistent pagination, sparse fieldsets, filtering, sorting.
5. Integration Layer: Outbound connectors (optional) for external read‑later services (webhooks or queued push).
6. Client(s): Decoupled Vue SPA (repo separation) consuming public API only.
7. Observability: Centralized logging, metrics, and rate limiting per capability.

Principles:
- Backwards compatible API evolution (additive first, remove with deprecation windows).
- Idempotent write operations where practical (e.g., marking read). 
- Clear separation of concerns (no view logic in controllers used by API).
- Lean internal “saved for later” (low friction) + optional external sync plugin.

---

## Remaining Roadmap (Proposed Phased Plan)

### Phase 2: Core Mutation Endpoints
Goal: Parity for essential user actions via API.
- Endpoints: mark post read/unread (single & bulk), mark all in source/category up to a timestamp, toggle/save (lightweight internal flag), dismiss/archive.
- Request validation objects + idempotency safeguards.
- Expand tests (happy path, unauthorized, rate limiting, idempotent repeat calls).
- Introduce basic token "capabilities" scaffolding (scopes stored with tokens, enforcement optional this phase but structure ready).

### Phase 3: Feed & Category Management APIs
Goal: Allow external client to manage sources fully.
- CRUD for Sources (add, rescan, mute/disable, delete) with validation + feed URL normalization & discovery (fetch & auto-detect if given a webpage URL).
- CRUD for Categories (create, rename, reassign posts/sources).
- Background job to validate and categorize new source after creation.
- Tests for feed validation errors (unreachable, invalid XML, duplicates).

### Phase 4: Enhanced Auth & Security
- Token scopes (read_posts, write_posts, manage_sources, summaries, admin) enforced.
- Per-scope rate limits; summary limiter refined to per-token basis.
- Audit log (DB table) for sensitive mutations (source creation, token creation, feed deletion).
- Optional token expiration & revocation UI improvements (copy button, last used timestamp).

### Phase 5: Vue SPA Extraction (Separate Repository)
- New standalone repo (e.g., `infraread-frontend`).
- Auth: Personal access token flow & local storage handling.
- Feature parity checklist (reading list, keyboard shortcuts, filtering, quick bulk mark read).
- API client abstraction + offline optimistic UI for read/unread toggles.
- Incremental rollout: behind feature flag; keep legacy UI until parity reached.

### Phase 6: Background Processing & Performance
- Queue feed fetch & plugin processing (one job per source; concurrency control & jitter to avoid thundering herd).
- Introduce feed fetch status metrics (last fetched at, duration, error count, consecutive failures).
- Add indexes (e.g., posts(read, source_id, category_id, published_at), summaries(post_id)).
- Implement exponential backoff for failing sources.

### Phase 7: External Read-It-Later Integration (Optional Plugins)
- Abstract “save” action: internal flag + dispatch integration job.
- Pluggable connectors (Pocket, Instapaper, Readwise Reader) with per-user credentials.
- Outbound retry & failure queue; user-visible sync status.
- Provide webhook/event format for third-party consumers (e.g., post_saved).

### Phase 8: Observability & Maintenance
- Structured logging (context: source_id, post_id) for ingestion pipeline.
- Basic metrics endpoint or integration (Prometheus format) for feeds processed/minute, failures, queue latency.
- Health endpoint & readiness probe (even without Docker, useful for orchestration / uptime monitors).
- Error budget / SLO notes for feed freshness.

### Phase 9: Public Developer Documentation & Versioning
- Formal API reference (OpenAPI spec generation) + changelog + deprecation policy.
- Quickstart guides (Create token, list posts, mark read, get summary).
- Example client snippets (JS/TS, curl).

### Phase 10: Hardening & Polishing
- Pagination strategy alignment (cursor-based for large sets?).
- Consistent error format (RFC 7807 style maybe) across endpoints.
- Bulk operations performance tuning.
- Caching layer (ETag / Last-Modified on GET endpoints) + conditional requests.

---

## Cross-Cutting Tasks & Technical Debt To Address
- Refine test suite: isolate fast unit tests vs feature/API tests; add factories for tokens & sources.
- Consolidate plugin lifecycle (pre-fetch vs post-fetch vs post-store hooks) with clear contracts.
- Introduce DTO / API Resource normalization layer to reduce controller duplication.
- Define serialization policy (fields whitelist, sparse fieldsets via `?fields[posts]=id,title,...`).
- Rate limiter strategy centralization (config-driven, keyed by token scopes).
- Data retention / pruning policy (archiving old posts, summary regeneration rules).

---

## External Read-It-Later Strategy (Preference Acknowledged)
- Keep internal "save" minimal (boolean + timestamp) for UI speed.
- Provide background job that maps saved posts to outbound integration queue.
- Allow per-user selection of provider & credentials; failure isolation (invalid token does not block internal save).
- Expose integration status via lightweight `/api/v1/integrations` endpoint later.

---

## Non-Goals / Explicit Exclusions (For Now)
- Dockerization (explicitly declined; keep instructions native). Document optional future containerization separately if ever needed.
- Building a proprietary complex offline read-later engine (outsourced to 3rd parties via integration layer).
- Premature microservices split (stay within single codebase until clear scaling pain, rely on modular boundaries inside Laravel).

---

## Immediate Next Step (When Work Resumes)
Start Phase 2:
1. Design mutation endpoint contracts (request/response JSON + error shapes) & add to this file / OpenAPI draft.
2. Implement mark read/unread (single, bulk) with tests.
3. Add lightweight internal save toggle + tests.
4. Introduce token scope columns & migration (populate existing tokens with *all* scopes for backward compatibility) – enforcement optional initially.

---

## Tracking Matrix

| Area | Status | Notes |
|------|--------|-------|
| Read endpoints (posts, sources, categories) | Done | Phase 1 baseline |
| Summary endpoint & rate limit | Done | POST endpoint implemented |
| Sanctum auth + token issuance UI | Done | Basic create/revoke by name |
| Admin layout responsive fix | Done | md breakpoint alignment |
| API tester page | Done | Temporary dev tool |
| Mutation endpoints | Pending | Phase 2 |
| Source/category CRUD via API | Pending | Phase 3 |
| Token scopes & granular limits | Pending | Phase 4 |
| Vue SPA extraction | Pending | Phase 5 |
| Queue & ingestion optimization | Pending | Phase 6 |
| External read-it-later integrations | Pending | Phase 7 |
| Observability & metrics | Pending | Phase 8 |
| OpenAPI & public docs | Pending | Phase 9 |
| Performance & caching polish | Pending | Phase 10 |

---

## Open Questions (To Clarify Later)
- Scope granularity: Do we need per-endpoint scopes or broader functional groups sufficient?
- Bulk limits: Max posts allowed in a single mark-read operation?
- Summary model retention: Regenerate strategy when original content updates?
- Integration triggers: Immediate async job vs batched schedule for external read-later sync?

---

## Maintenance Notes
- Keep this document updated at the end of each completed phase (append changelog segment rather than rewriting history).
- When introducing breaking API changes, record deprecation timeline here first.

---

Last Updated: 2025-08-12


