# Objectives

Refactor the existing monolithic Laravel RSS reader iPrinciples:
- Backwards compatible API evolution (additive first, remove with deprecation windows).
- Idempotent write operations where practical (e.g., marking read). 
- Clear separation of concerns (no view logi### Phase 7: External Read-It-Later Integration (Post-Migration)
- Abstract "save" action: internal flag + dispatch integration job.
- Pluggable connectors (Pocket, Instapaper, Readwise Reader) with per-user credentials.
- Outbound retry & failure queue; user-visible sync status.
- Provide webhook/event format for third-party consumers (e.g., post_saved).
- **Prerequisites**: Complete Phase 5 frontend migration to validate API and provide testing client.controllers used by API).
- Simple two-state post model: read (archived) or unread (inbox) - no additional states needed.
- Client-side integration with external read-later services rather than internal storage. cleanly separated system:

1. A backend service exposing a stable, versioned, token‑protected JSON API that ingests, processes, organizes, enriches (summaries, plugins) and stores feed data.
2. One (later multiple) independent frontend clients (initially a Vue SPA in a separate repository) that consume only the public API for all read & write operations, preserving existing UX (keyboard shortcuts, fast navigation).
3. Extensibility for additional platform clients (mobile / desktop) without further backend coupling.

---

## Current Structure (Baseline Before Refactor)

Single Laravel app handling:
- Scheduled/Cron RSS fetch & plugin processing (store normalized posts, media, categories, sources).
- Server-rendered & Vue-enhanced web UI (reading, mark read/unread, save for later, subscribe to feeds, AI summarize, etc.) using bespoke endpoints tightly coupled to views.
- **OPML Import/Export functionality** for seamless RSS reader migration (setup workflow + source management).
- Plugin system for post‑processing (e.g., fixing relative links, legibility, marking read, text transformations).

## Critical Functionality to Preserve

### **OPML Import/Export (Essential for RSS Reader Migration)**
- **OPML Export**: Download all sources as standardized OPML file (`/feeds.opml`) for backup or migration to other RSS readers
- **OPML Import**: Upload OPML file during setup workflow to bulk-import feeds from other RSS readers
- **Current Implementation**: 
  - Export: Simple route generating XML view with categories/sources
  - Import: File upload + `OpmlImporter` utility class with XML parsing
  - Setup integration: First-run onboarding flow includes OPML upload option
- **API Evolution Strategy**: Maintain web routes for direct download/upload, add API endpoints for programmatic access
- **Migration Compatibility**: Support standard OPML format used by major RSS readers (Feedly, Inoreader, etc.)

Constraints & Preferences:
- No Docker for local dev (run natively on host macOS; keep setup simple: PHP + Composer + Node). 
- Prefer integrating with an existing 3rd‑party "read it later" ecosystem (e.g., Pocket / Readwise / Instapaper) rather than building a heavy proprietary read‑later silo. Internal flagging can exist but external export/integration is the strategic direction.
- Maintain plugin architecture; future plugins should be API‑triggerable where meaningful.
- Favor incremental, test‑backed extraction over big bang rewrite.
- Queue driver: use Laravel's database queue driver (no Redis/Horizon required). Run a persistent queue worker via Supervisor/systemd; ensure `jobs` and `failed_jobs` tables exist.

---

## Progress So Far (Phases 1–3 Complete)

Backend API Foundations (Read / Derived Data - Phase 1):
- Introduced versioned API namespace (`/api/v1`).
- Implemented authenticated read‑only endpoints for posts, sources, categories (filtering / includes retained where applicable).
- Added on‑demand AI summary endpoint (`POST /api/v1/posts/{post}/summary`) with rate limiting.
- Integrated Laravel Sanctum personal access tokens; all v1 endpoints protected by `auth:sanctum`.
- Implemented rate limiter group for summary generation.

Core Mutation Endpoints (Phase 2):
- Single post read/unread operations (`PATCH /api/v1/posts/{post}/read-status`) with idempotent behavior.
- Bulk operations (`PATCH /api/v1/posts/bulk-read-status`) supporting up to 1000 posts with database transactions.
- Efficient mark-all operations (`PATCH /api/v1/posts/mark-all-read`) with optional filtering by source, category, and date.
- Comprehensive Form Request validation classes with custom error messages.
- Performance-optimized queries that only update posts requiring state changes.

Auth & Developer Experience:
- Built minimal in‑app API token management page (generate & revoke tokens) under admin UI
- Added temporary API Tester Blade page allowing manual requests with Bearer token injection (facilitates manual QA before external client exists).

Documentation & Housekeeping:
- Updated `README` with Phase 1 API usage, auth instructions, and summary endpoint details.
- Milestone commit created capturing stable Phase 1 baseline.
- Refactored views to unify admin layout; cleaned up CSS linking issues.

Testing & Reliability:
- Comprehensive feature test suite (42 tests, 165 assertions) covering authentication, validation, edge cases, and idempotency.
- Tests validate proper error handling, bulk operation limits, filtering combinations, and performance optimizations.
- All Phase 1 & 2 endpoints fully tested with both happy path and error conditions.

Architecture Insights Captured:
- Tailwind custom screen config currently only defines `md` (600px); design choices adjusted accordingly.
- `User` model updated (`HasApiTokens`) enabling token issuance with fillable adjustments.
- Established patterns for idempotent operations, bulk processing, and efficient database updates.

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

### Phase 2: Core Mutation Endpoints ✅ COMPLETE
Goal: Parity for essential user actions via API.
- ✅ Endpoints implemented:
  - Mark single post read/unread (`PATCH /api/v1/posts/{post}/read-status`)
  - Mark posts read/unread (bulk with specific IDs for user selections) (`PATCH /api/v1/posts/bulk-read-status`)
  - Mark all posts read/unread (efficient bulk operations with optional filtering by source/category/date) (`PATCH /api/v1/posts/mark-all-read`)
- ✅ Request validation objects + idempotency safeguards implemented.
- ✅ Comprehensive test suite (happy path, unauthorized, rate limiting, idempotent repeat calls).
- ✅ Posts maintain two-state model: read (archived) or unread (inbox).
- ✅ "Save for Later" remains client-side responsibility (external read-later services).
- ✅ Bulk operations use Laravel's efficient query builder methods for optimal performance.

### Phase 3: Feed & Category Management APIs ✅ COMPLETE
**Objective:** Enable programmatic management of RSS feeds and categories.

#### ✅ Completed Features:

**Source Management API**
- `POST /api/v1/sources` - Create new RSS feed source with automatic discovery
- `PUT /api/v1/sources/{id}` - Update existing source
- `DELETE /api/v1/sources/{id}` - Remove source and all its posts  
- `POST /api/v1/sources/{id}/refresh` - Force refresh posts from source
- Enhanced UrlAnalyzer class with robust error handling
- Database constraints prevent duplicate RSS URLs
- 11 comprehensive tests covering all scenarios

**Category Management API ✅**
- `GET /api/v1/categories` - List all categories with source counts
- `GET /api/v1/categories/{id}` - Show category with sources
- `POST /api/v1/categories` - Create new category
- `PUT /api/v1/categories/{id}` - Update category  
- `DELETE /api/v1/categories/{id}` - Remove category (automatically moves sources to "Uncategorized")
- Form validation with uniqueness constraints
- Smart deletion handling with source migration
- 16 comprehensive tests covering all scenarios

**OPML Import/Export API ✅**
- `GET /api/v1/export-opml` - Export all sources as OPML format
- `POST /api/v1/preview-opml` - Preview OPML file before import
- `POST /api/v1/import-opml` - Import sources from OPML file
- Supports both "replace" and "merge" import modes
- Full validation and error handling
- 11 comprehensive tests covering all scenarios
- Preserves existing web-based OPML functionality

**Implementation Highlights:**
- **OpmlExporter**: Generates standard OPML 2.0 format with category structure
- **OpmlImporter**: Enhanced with preview mode, merge capability, proper validation
- **CategoryController**: Full CRUD with smart deletion (moves orphaned sources to "Uncategorized")
- **Form Requests**: Comprehensive validation for all operations
- **Database Transactions**: Ensures data integrity during complex operations
- **Legacy Support**: Maintains existing web routes and functionality
- **Security**: XML validation, file size limits, proper error handling
- **API Documentation**: Complete API reference with examples and error handling

**Total Phase 3 Tests: 38 (11 Source + 16 Category + 11 OPML)**

**📖 Documentation:** See `API-REFERENCE.md` for complete endpoint documentation

### Phase 4: Enhanced Auth & Security (Deferred)
- Deferred for single-user project. See "Possible Future Improvements" below.

### Phase 5: Frontend API Migration (In-Place Refactor)
**Objective:** Convert existing Vue frontend to consume the API instead of direct database calls, while maintaining current functionality and UX.

#### Frontend Audit Findings:

**Current Architecture:**
- **Mixed approach**: Session auth + direct DB queries + some legacy API calls
- **Two main domains**: `/app` (reader interface) and `/admin` (administration)
- **Vue.js frontend**: Main `App.vue` component with child components (Post, PostItem, SummarizeButton, etc.)
- **Livewire components**: Admin functionality (SourceList, Muted)
- **Multiple API layers**: Legacy routes in `infraread_api.php` + new V1 API

**Critical Issues Identified:**
1. **Inconsistent API usage**: App.vue mixes old legacy APIs with direct routes
2. **Authentication gap**: Frontend uses session auth, API uses Sanctum tokens
3. **Missing API coverage**: Some admin functionality lacks V1 API endpoints
4. **Legacy route dependencies**: Direct database queries in web routes

#### Migration Strategy:

**Phase 5A: API Client Foundation ✅ COMPLETE**
- ✅ **Unified JavaScript API client** (`resources/js/api/client.js`) with comprehensive error handling
- ✅ **Simplified authentication system**: Laravel generates Sanctum tokens, passes via `window.Laravel.apiToken`
- ✅ **Flexible token management**: Priority system (.env token → auto-generated → localStorage fallback)
- ✅ **HTTP request standardization**: Retry logic, error boundaries, consistent authentication headers
- ✅ **API client test component**: Real-time validation of API connectivity and authentication
- ✅ **Clean codebase**: Removed complex session-to-token bridging, simplified architecture

**Implementation Highlights:**
- **Token Priority System**: `INFRAREAD_API_TOKEN` from .env → auto-generated per page load → localStorage fallback
- **InfrareadAPI Class**: Complete HTTP client with authentication, retries, error handling, and all V1 endpoints
- **Blade Integration**: Token injection via `window.Laravel.apiToken` for seamless frontend access
- **Error Handling**: Custom `APIError` class with user-friendly messages and structured error data
- **Developer Experience**: Console logging, authentication status display, comprehensive API testing interface

**Architecture Benefits:**
- **Performance**: .env tokens eliminate repeated generation overhead
- **Flexibility**: Supports both development (auto-generation) and production (stable tokens) workflows
- **Reliability**: No complex session bridging, direct Sanctum token usage
- **Maintainability**: Clean, focused codebase with clear separation of concerns

**Phase 5B: Reader Interface Migration (`/app` domain)**
- **App.vue data fetching**: Replace `/api/{which}` with `/api/v1/posts` filtering
- **Read status updates**: Migrate `/api/posts/{id}` to `/api/v1/posts/{id}/read-status`
- **Summary generation**: Replace `/summary/{post}` with `/api/v1/posts/{id}/summary`
- **Bulk operations**: Implement mark-all-read using `/api/v1/posts/mark-all-read`
- **Source switching**: Use `/api/v1/posts?filter[source]={id}` for source-specific views

**Phase 5C: Authentication System Migration**
- ✅ **Dual auth support**: Session auth for web + automatic API token generation
- ✅ **Token management**: Auto-generate tokens for logged-in users with .env override option
- ✅ **Frontend token handling**: Tokens passed via `window.Laravel.apiToken` in Blade template
- ✅ **Error handling**: Clean 401 handling with token clearing, no complex retry loops

**Phase 5D: Administration Interface Migration (`/admin` domain)**
- **AdminSourceController**: Replace Eloquent calls with `/api/v1/sources` endpoints
- **AdminCategoryController**: Migrate to `/api/v1/categories` endpoints  
- **Livewire components**: Update SourceList and Muted to consume API
- **Token management UI**: Already implemented, validate API integration

#### Specific Database Interactions to Replace:

**Reader Interface (Vue Components):**
```javascript
// BEFORE (legacy)
axios.get("/api/" + this.which_posts)
axios.patch("/api/posts/" + p.id, { read: 1 })
axios.get("/summary/" + this.post)

// AFTER (V1 API)
this.api.getPosts({ filter: { read: false, source: sourceId } })
this.api.markPostRead(postId, true)
this.api.generateSummary(postId, sentences)
```

**Admin Interface (Controllers):**
```php
// BEFORE (direct Eloquent)
Source::with('Category')->get()
$source->update($request->except(['_token']))
Category::all()

// AFTER (HTTP API calls)
$this->apiClient->get('/sources?include=category')
$this->apiClient->put("/sources/{$id}", $data)
$this->apiClient->get('/categories')
```

**Legacy Routes to Eliminate:**
- `/api/{which}/{details?}` → Replace with `/api/v1/posts` filtering
- `/markallread` → Use `/api/v1/posts/mark-all-read`
- `/summary/{post}` → Use `/api/v1/posts/{id}/summary`
- Direct model queries in routes → HTTP API calls

#### Implementation Priority:
1. **Start with Reader Interface** (App.vue) - most API coverage exists, immediate validation
2. **API Client Foundation** - reusable HTTP client with auth handling
3. **Authentication Bridge** - session-to-token conversion for seamless migration
4. **Admin Interface** - more complex, requires additional API endpoint coverage

#### Benefits of In-Place Migration:
- **API Validation**: Real usage immediately reveals API gaps and performance issues
- **Risk Reduction**: Incremental migration with immediate feedback
- **UX Preservation**: Maintains existing user experience during transition
- **Foundation Building**: Creates perfect testing ground for external integrations (Phase 7)
- **Performance Testing**: Real user interactions reveal optimization needs

### Phase 6: Background Processing & Performance ✅ COMPLETE
**Objective:** Enhanced feed processing with improved error handling, metrics tracking, performance monitoring, and comprehensive background job system.

#### ✅ Completed Work:

**Enhanced Feed Processing Architecture**
- **Source Health Metrics**: Added comprehensive tracking fields (last_fetched_at, last_fetch_duration_ms, consecutive_failures, last_error_at, last_error_message, status)
- **Database Performance**: Added strategic indexes for posts (read + source_id + category_id, posted_at) and source metrics queries
- **Structured Exception Handling**: Created dedicated exception hierarchy (FeedFetchException, FeedParseException, PluginException) with context preservation
- **Source Health Monitoring**: Implemented exponential backoff for failed sources, health status tracking (active, failing, failed)
- **Performance Tracking**: Capture and store feed processing duration, success/failure metrics, and error details

**Improved Error Handling & Resilience**
- **Custom Exception Classes**: 
  - `FeedProcessingException` base class with source context tracking
  - `FeedFetchException` for HTTP errors, timeouts, invalid URLs with retryability logic  
  - `FeedParseException` for XML parsing errors, missing elements, empty feeds
  - `PluginException` for plugin configuration and execution errors
- **Exponential Backoff**: Intelligent retry scheduling for failed sources (2^failures minutes up to 24 hours)
- **Structured Error Tracking**: Preserve full error context including HTTP status, XML parsing details, plugin failures
- **Health Status System**: Active → Failing → Failed progression with automatic recovery on success

**Enhanced Source Model**
- **Metrics Methods**: `getSourceMetrics()`, `shouldSkipDueToBackoff()`, `getNextAttemptTime()`, `getHealthSummary()`
- **Query Scopes**: `healthy()`, `failing()`, `failed()` for filtering sources by health status
- **Status Descriptions**: Human-readable health status explanations for monitoring and debugging
- **Protected Health Tracking**: Comprehensive `recordSuccessfulUpdate()` and `recordFailedUpdate()` methods

**Plugin System Overhaul** ✅ COMPLETE
- **Enhanced Plugin Kernel**: Structured configuration system with options support, legacy compatibility, validation capabilities
- **Updated Plugin Interface**: Added `getMetadata(): array` method, enforced `handle(): bool` return type, options support
- **All Plugins Updated**: FixRelativeLinks (complete rewrite), MakeTextLegible (enhanced), MarkPostAsRead (configurable), ReplaceArticleLink (enhanced), Sample (template)
- **Comprehensive Testing**: 13-test suite covering configuration, error handling, metadata, options support, performance
- **Management Command**: `plugins:manage` with list, validate, test, and sources subcommands
- **Enhanced Post Processing**: Improved `applyPlugins()` method with structured error handling, context preservation, success tracking

**Value Objects & Structured Returns**
- **SourceUpdateResult**: Immutable value object for feed processing results with success/failure handling
- **Factory Methods**: `success()` and `failure()` constructors with consistent data structure
- **Human-Readable Summaries**: Formatted result descriptions for logging and command output
- **API-Ready Conversion**: `toArray()` method for structured API responses

**Updated Console Command**
- **Enhanced PostsUpdater**: Improved error handling, comprehensive progress reporting, metrics integration
- **Intelligent Processing**: Respects exponential backoff, skips sources in cooldown period
- **Detailed Logging**: Structured logs for monitoring, performance tracking, and debugging
- **Progress Indicators**: Real-time status updates with emojis and clear success/failure reporting
- **Performance Summary**: Complete processing statistics with duration, success rates, and error counts

**Comprehensive Testing**
- **Unit Test Coverage**: 47 tests covering exceptions, source metrics, value objects, plugin system, and edge cases
- **Exception Testing**: Validation of error context preservation, retryability logic, and factory methods
- **Metrics Testing**: Source health tracking, exponential backoff calculations, and query scopes
- **Value Object Testing**: Immutability, factory methods, serialization, and readonly properties
- **Plugin System Testing**: 13 tests covering configuration, error handling, metadata, options support, performance validation
- **Timing & Duration Tests**: Microsecond precision testing with proper timing delays

**Database Migrations Applied**
- **Source Metrics Migration**: Added tracking fields with proper defaults and indexes
- **Posts Performance Migration**: Strategic indexes for read status filtering and date-based queries
- **Constraint Updates**: Enhanced data integrity and query performance

**Implementation Highlights:**
- **Backward Compatibility**: All existing functionality preserved, new features are additive
- **Error Boundaries**: Clear separation between different types of processing failures
- **Performance Focus**: Minimal overhead for successful operations, detailed tracking for failures
- **Monitoring Ready**: Rich metrics and logging for production observability
- **Developer Experience**: Clear error messages, structured logs, and comprehensive test coverage

**Foundation for Future Phases:**
- Queue job infrastructure ready for user-triggered operations (manual refresh, AI summaries)
- Metrics infrastructure supports future observability and monitoring endpoints
- Exception handling provides foundation for retry mechanisms and external service integration
- Performance tracking enables data-driven optimization decisions
- **Plugin System**: Structured configuration and comprehensive error handling ready for API-triggered operations

**Laravel Jobs Implementation:**
- **RefreshSourceJob**: Complete background job for manual source refresh with proper retry logic
- **GenerateSummaryJob**: Full AI summary generation job with caching and status tracking  
- **JobController API**: Endpoints for job dispatch (`POST /api/v1/jobs/sources/{id}/refresh`, `POST /api/v1/jobs/posts/{id}/summary`)
- **Status Monitoring**: Cache-based status tracking (`GET /api/v1/jobs/summary/{cache_key}/status`, `GET /api/v1/jobs/queue/status`)
- **Queue Configuration**: Separate queues ('refresh', 'summaries'), exponential backoff, proper timeouts
- **Comprehensive Testing**: 47+ unit tests covering all job scenarios, error handling, retry logic, and cache management

**Total Phase 6 Tests: 47+ unit tests (all passing)**

**Teaching Notes:**
This foundation work demonstrates several key Laravel concepts:
- **Exception Design**: Creating domain-specific exceptions with factory methods and context preservation
- **Database Design**: Strategic indexing and performance considerations for real-world query patterns  
- **Value Objects**: Immutable data structures for structured returns and API consistency
- **Model Enhancement**: Adding business logic methods while maintaining clean separation of concerns
- **Plugin Architecture**: Structured configuration, interface design, and comprehensive error handling
- **Testing Strategy**: Comprehensive unit testing with proper mocking and timing considerations
- **Command Design**: Building robust console commands with progress reporting and error handling

The approach builds upon existing proven patterns (cron-based processing) rather than replacing them, showing how to enhance existing systems incrementally rather than rewriting from scratch.

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
## Possible Future Improvements

### Enhanced Auth & Security (Deferred)
- Audit log (DB table) for sensitive mutations (source creation, token creation, feed deletion).
  - Add audit_logs table (actor_id, action, subject_type/id, metadata JSON, ip, user_agent, occurred_at).
  - Log events in an AuditLogger service via listeners (source create/update/delete, category CRUD, token create/revoke).
  - Tests: rows inserted with correct metadata; idempotent/no duplicates on retries.
- Token UX and safety
  - Optional token expiration (expires_at); enforce in auth middleware; pruning job.
  - Show last used timestamp; add copy button in UI; revoke with confirmation.
  - Rate limit token operations per user.
- Centralized rate limiting for management endpoints
  - Named limiters for sources/categories/opml and summary generation; consistent error payloads.
  - Tests for headers and throttling behavior.

These are valuable for multi-user hardening and can be implemented later without breaking the public API.

---

## Cross-Cutting Tasks & Technical Debt To Address
- **🔄 Frontend API Migration**: Replace legacy `/api/` routes and direct database queries with V1 API calls in App.vue and admin interfaces
- **🔐 Authentication Bridge**: Implement session-to-token conversion for seamless API authentication
- **📱 API Client Standardization**: Create unified JavaScript HTTP client for consistent API interactions
- Refine test suite: isolate fast unit tests vs feature/API tests; add factories for tokens & sources.
- Consolidate plugin lifecycle (pre-fetch vs post-fetch vs post-store hooks) with clear contracts.
- Introduce DTO / API Resource normalization layer to reduce controller duplication.
- Define serialization policy (fields whitelist, sparse fieldsets via `?fields[posts]=id,title,...`).
- Rate limiter strategy centralization (config-driven, per-user basis for summary generation).
- Data retention / pruning policy (archiving old posts, summary regeneration rules).
- **📬 Update Postman Collection**: Add Phase 5 migration endpoints and authentication examples
- **🧪 Enhance API Tester**: Validate V1 API compatibility with frontend migration requirements

---

## External Read-It-Later Strategy (Preference Acknowledged)
- Client applications handle "save for later" directly by integrating with external read-later services (Pocket, Instapaper, Readwise, etc.) using their respective APIs.
- No internal "saved" state stored in database - this is purely a frontend responsibility.
- Infraread API provides post URLs and metadata needed for external service integration.
- Each client can implement their preferred read-later service integration independently.

---

## Non-Goals / Explicit Exclusions (For Now)
- Dockerization (explicitly declined; keep instructions native). Document optional future containerization separately if ever needed.
- Building a proprietary complex offline read-later engine (outsourced to client-side integration with 3rd party services).
- Internal "saved for later" database storage (handled client-side via external service APIs).
- Premature microservices split (stay within single codebase until clear scaling pain, rely on modular boundaries inside Laravel).

---

## Immediate Next Step (When Work Resumes)
Continue Phase 5 (Frontend API Migration):
1. ✅ **API Client Foundation**: Unified JavaScript HTTP client (`resources/js/api/client.js`) complete
2. ✅ **Authentication Bridge**: Simplified token system (Laravel-generated → .env override) complete
3. **Reader Interface Migration**: Replace App.vue legacy API calls with V1 endpoints
4. **Admin Interface Migration**: Convert admin controllers and Livewire components to API consumption
5. **Legacy Route Elimination**: Remove direct database queries from web routes
6. **Error Handling & UX**: Implement proper API error handling and user feedback

**Implementation Priority**: Start with Reader Interface (App.vue) since API client foundation is complete and most API coverage exists.

**Current Status:** Phase 5A complete. API client foundation implemented with flexible token management (.env → auto-generated → localStorage), comprehensive error handling, and clean authentication architecture. Ready to begin App.vue migration to V1 API endpoints.

**Key Architecture Decisions:**
- Simplified token approach: Laravel generates/injects tokens, no complex session bridging
- Priority token system: .env override for production stability, auto-generation for development
- Clean error handling: No retry loops, clear 401 responses, user-friendly error messages
- Comprehensive API client: All V1 endpoints implemented with proper TypeScript-like structure

Note: Simple two-state model: posts are either read (archived) or unread (inbox). No additional dismiss/archive states.
Note: "Save for Later" functionality is handled client-side by integrating directly with external read-later services, not via API endpoints.
Note: Single-user application - all tokens have full access, no scope restrictions needed.

---

## Tracking Matrix

| Area | Status | Notes |
|------|--------|-------|
| Read endpoints (posts, sources, categories) | Done | Phase 1 baseline |
| Summary endpoint & rate limit | Done | POST endpoint implemented |
| Sanctum auth + token issuance UI | Done | Basic create/revoke by name |
| Admin layout responsive fix | Done | md breakpoint alignment |
| API tester page | Done | Temporary dev tool |
| Single post read/unread mutation | Done | Phase 2 - idempotent operations |
| Bulk post operations (by IDs) | Done | Phase 2 - up to 1000 posts, transactions |
| Mark-all operations with filters | Done | Phase 2 - efficient query optimization |
| Comprehensive mutation testing | Done | Phase 2 - 42 tests, 165 assertions |
| Source/category CRUD via API | Done | Phase 3 - Full CRUD with 38 tests |
| OPML Import/Export API | Done | Phase 3 - Complete migration functionality |
| API Reference Documentation | Done | Complete endpoint documentation with examples |
| Enhanced auth & audit logging | Deferred | Moved to "Possible Future Improvements" |
| API Client Foundation (Phase 5A) | Done | Unified JS client, simplified auth, token management |
| Vue SPA extraction | In Progress | Phase 5B-D: Reader interface migration next |
| Queue & ingestion optimization | Done | Phase 6 complete: background jobs, metrics, error handling, plugin system, comprehensive job testing |
| External read-it-later integrations | Pending | Phase 7 |
| Observability & metrics | Pending | Phase 8 |
| OpenAPI & public docs | Pending | Phase 9 |
| Performance & caching polish | Pending | Phase 10 |

---

## Open Questions (To Clarify Later)
- Bulk limits: Max posts allowed in a single mark-read operation?
- Summary model retention: Regenerate strategy when original content updates?
- Integration triggers: Immediate async job vs batched schedule for external read-later sync?

---

## Maintenance Notes
- Keep this document updated at the end of each completed phase (append changelog segment rather than rewriting history).
- When introducing breaking API changes, record deprecation timeline here first.
- **📖 API Documentation:** Always update `API-REFERENCE.md` when adding new endpoints, changing request/response formats, or modifying authentication requirements.
- **🧪 Developer Tools:** Keep `/api-tester` tool and `postman/infraread-phase1-api.postman_collection.json` updated with new endpoints for easy testing and integration.
 - Queue worker: Ensure a persistent worker is running (database driver). Monitor logs and set sensible `--timeout` and `--tries`; without a worker, queued jobs will not run.

---
## AI Approach
While working, always explain what you are doing so that the coder can learn. The coder is intermediate level in Laravel and needs guidance with advanced architecture concepts, design patterns, testing strategies, and API best practices. 

**Teaching Focus Areas:**
- API design principles and RESTful patterns
- Laravel-specific patterns (Form Requests, Resources, Service classes)
- Testing strategies (feature vs unit tests, test organization)
- Database design decisions and migration patterns
- Error handling and validation approaches
- Code organization and separation of concerns
- **Background jobs and queues** (explain in simple, non-intimidating terms)
- **Service layer architecture** and dependency injection
- **External API integration** and HTTP client usage
- **API Documentation:** Always update `API-REFERENCE.md` when implementing new endpoints
- **Developer Tools:** Keep `/api-tester` and Postman collection updated with new endpoints

**Special Emphasis on Background Jobs:**
When teaching queues and background jobs, use simple analogies and step-by-step explanations:
- Start with "why" before "how" (user experience benefits)
- Use real-world analogies (restaurant kitchen, post office, etc.)
- Explain each concept in isolation before combining them
- Show both simple and complex examples with clear progression
- Address common concerns about complexity and debugging
- Provide practical examples relevant to RSS feed processing
- **Hybrid Approach**: Build on existing cron-based feed processing rather than replacing it
- Demonstrate when to use jobs vs cron vs immediate processing for different scenarios
- Show how Laravel jobs complement existing reliable scheduled processing

Provide context for why specific approaches are chosen, explain trade-offs, and highlight Laravel conventions and best practices throughout the implementation process.


Last Updated: 2025-08-23 (Phase 5A Complete - API Client Foundation)


