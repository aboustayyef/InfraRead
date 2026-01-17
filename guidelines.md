# InfraRead Codebase Guidelines

This document captures the existing structure, patterns, and conventions observed in the InfraRead codebase. Use it to keep new work consistent with current architecture and style.

## High-Level Structure

- **Laravel application (v12) with classic Laravel 10-style structure** (no new streamlined Laravel 11+ structure).
- **Backend:** PHP/Laravel, Sanctum for API auth, queued jobs for long-running tasks.
- **Frontend:** Vue 2 app for the main UI and admin views, Alpine.js (v2), Tailwind CSS (v2).
- **Build tooling:** Laravel Mix + Webpack (`webpack.mix.js`), not Vite.
- **API versioning:** `/api/v1` with resources and form requests.

Key top-level folders:
- `app/` (application code)
- `routes/` (web, api, onboarding)
- `resources/` (Blade views, Vue components, JS, CSS)
- `database/` (migrations, factories, seeders)
- `tests/` (PHPUnit feature/unit tests)

## Backend Organization and Patterns

### App Directory Layout
- `app/Http/Controllers/` includes standard controllers and API v1 controllers.
- `app/Http/Requests/Api/V1/` contains Form Request validation classes.
- `app/Http/Resources/` contains API resources (JsonResource).
- `app/Models/` contains Eloquent models.
- `app/Jobs/` contains queued jobs (e.g., summary generation, source refresh).
- `app/Plugins/` contains the plugin system (`Kernel` + `Plugin*` classes).
- `app/Fetchers/` contains RSS fetching logic (`rssFetcher`).
- `app/Utilities/` contains standalone helpers (OPML, API token resolver, read-later integrations).
- `app/ValueObjects/` contains small immutable value types (e.g., `SourceUpdateResult`).
- `app/View/Components/` contains Blade component classes for layouts.

### Controllers and Requests
- API controllers are grouped under `App\Http\Controllers\Api\V1` and use `routes/api.php`.
- Validation for API endpoints uses Form Requests in `app/Http/Requests/Api/V1` with array-based rules and custom messages.
- API responses are returned through `JsonResource` classes (`app/Http/Resources`).
- API endpoints accept `include` query params to load relations (`source`, `category`) and use `filter[...]` params for server-side filtering.

### Models and Eloquent
- Models live in `app/Models` and primarily use `$casts`, `$guarded`, and relationships directly.
- Relationships are declared with standard Eloquent methods (e.g., `belongsTo`, `hasMany`).
- Some models include utility methods (e.g., `Post::summary()`, `Post::applyPlugins()`).
- The codebase currently mixes strict type usage: some newer classes use typed properties/return types, many older ones do not. Follow the local file’s style when extending.

### Plugin System
- Plugins are `App\Plugins\Plugin{Name}` and must implement `PluginInterface`.
- `App\Plugins\Kernel` maps sources to plugins and includes global plugins.
- `Post::applyPlugins()` handles plugin execution, logs failures, and continues on errors.

### Jobs and Queues
- Long-running tasks (e.g., AI summaries) use queued jobs in `app/Jobs`.
- Jobs set retries, backoff, and timeouts and often cache results (e.g., summary status).
- The default scheduler runs `app:update_posts` hourly (`app/Console/Kernel.php`).

### Utilities
- External integrations (Pocket, Instapaper, Omnivore, Narrator) are encapsulated in `app/Utilities/ReadLater`.
- OPML import/export lives in `app/Utilities/OpmlImporter`/`OpmlExporter`.
- API tokens are resolved via `app/Utilities/ApiTokenResolver`.

## Routes and API Conventions

- **Web routes**: `routes/web.php` and `routes/onboarding.php` (auth-protected UI, setup flow).
- **API routes**: `routes/api.php` with `/api/v1` prefix and `auth:sanctum` middleware.
- Legacy or deprecated endpoints are still referenced and annotated in routes.

## Frontend Conventions

### Vue 2 App
- Main Vue entry: `resources/js/app.js`.
- Components are in `resources/js/components`, with nested `partials/` for smaller pieces.
- Main app mounted on `#app` in `resources/views/home.blade.php`.
- API interactions go through `resources/js/api/client.js` (centralized client).

### Blade Views
- Blade layouts in `resources/views/layouts`.
- Vue app uses `mix()` and includes `resources/css/app.css` and `resources/js/app.js`.

### Styles
- Tailwind CSS v2, configured in `tailwind.config.js`.
- Custom design tokens: `primary` color, custom breakpoints, serif-based `sans` font using Roboto Slab.
- Custom utility classes in `resources/css/app.css` (e.g., `.ir_input`, `.ir_button`).

### Build Tooling
- Laravel Mix (`webpack.mix.js`) builds:
  - `resources/js/app.js` → `public/js/app.js`
  - `resources/js/admin.js` → `public/js/admin.js`
  - `resources/css/app.css` → `public/css/app.css`
- BrowserSync is configured to proxy `http://infraread.test`.

## Testing Conventions

- Tests use PHPUnit and live in `tests/Feature` and `tests/Unit`.
- Feature tests commonly use `RefreshDatabase` and factories.
- Factories are defined in `database/factories` and are used heavily in API tests.
- API tests assert JSON structure and authentication behavior.

## Database Conventions

- Migrations live in `database/migrations` with a long historical timeline.
- Factories set up associated `Source` and `Category` by default where needed.
- Seeders include `AdminSeeder` and are wired in `DatabaseSeeder`.

## Configuration

- App-specific configuration lives in `config/infraread.php`.
- External service keys are pulled from `config/services.php` and used in `ReadLater` and other utilities.

## Practical Tips for New Work

- Keep new API endpoints under `/api/v1` and return resources.
- Reuse existing utilities (OPML, API token handling, read-later integrations) instead of re-implementing.
- Prefer extending the Vue 2 component patterns and centralized API client.
- Respect the Laravel Mix pipeline and Tailwind v2 usage.
- When adding new backend logic, follow the style of nearby files (type hints vary by file age).

