<img src="public/img/infraread144.png" width="72" height="72">

# InfraRead

InfraRead (pronounced _infrared_ ) is an elegant self-hosted RSS feed reader and sync service. It is optimized for simplicity, reading and comfort. It is inspired by [Unread](https://itunes.apple.com/us/app/unread-rss-reader/id1252376153?mt=8) and built with [Laravel](https://laravel.com) and [VueJs](https://vuejs.org/).

<img src="public/img/screenshot.png" width="600" height="auto"> <br/>
<img src="public/img/screenshot-2.png" width="600" height="auto"> <br/>
<img src="public/img/screenshot-3.png" width="600" height="auto"> <br/>

## Features:

*   Distraction free. Attention to details and optimized for reading
*   Access From any device and feeds stay synced
*   Import your own OPML or add feeds manually
*   100% responsive
*   Full Keyboard Navigation using Vim Shortcuts (Key bindings can be changed)
*   Save For later, can choose between Instapaper and Pocket
*   Offline persistence of settings and previously loaded posts
*   **Complete REST API** for programmatic access and external client development

## API Documentation

InfraRead provides a comprehensive REST API for all functionality. See **[API-REFERENCE.md](API-REFERENCE.md)** for complete documentation including:

- Authentication with personal access tokens
- Posts management (read/unread, bulk operations, filtering)
- Sources and categories CRUD
- OPML import/export for RSS reader migration
- Rate limiting and error handling
- Complete examples and workflows

## Requirements

*  [Same Requirements as Laravel 8](https://laravel.com/docs/5.5/installation#server-requirements)
*  Ability to create Cron Jobs
*  Queue worker for background jobs (database queue driver recommended). In production (Ubuntu), run a persistent systemd service; in macOS development, run `php artisan queue:work` in a terminal.

## Installation

*  Download or clone the repository to your server 
*  run ```composer install```
*  run ```php artisan key:generate```
*  make a copy of ```.env.example``` and save as ```.env```
*  fill your admin details in ```.env```
*  fill your database details in ```.env```
*  run ```php artisan migrate``` to migrate the database
*  run ```php artisan db:seed```, this will populate your admin details in the database
*  add this line to your Crontab: ```* * * * * php /path/to/your/site/artisan schedule:run >> /dev/null 2>&1```
*  If you want to modify the js and css assets, also run: ```npm install```, and then ```npm run dev```

On macOS (development) without cron, you can run the scheduler loop instead:

```
php artisan schedule:work
```

## Queue worker (systemd service)

InfraRead uses Laravel's queue for background jobs (e.g., on‑demand feed refresh, summaries). With the database queue driver you don't need Redis/Horizon—just a long‑running worker.

1) Create a systemd service (adjust paths, user, and env):

File: `/etc/systemd/system/infraread-queue-worker.service`

```
[Unit]
Description=Infraread Laravel Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/infraread/current
Environment=APP_ENV=production
Environment=QUEUE_CONNECTION=database
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --timeout=120 --max-time=3600
Restart=always
RestartSec=5
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

2) Enable and start the service:

```
sudo systemctl daemon-reload
sudo systemctl enable --now infraread-queue-worker
sudo systemctl status infraread-queue-worker
```

Notes
- Ensure queue tables exist: `php artisan queue:table && php artisan migrate` (run once).
- Tune `--timeout`, `--tries`, and `--sleep` based on workload.
- Tail logs: `journalctl -u infraread-queue-worker -f`.
- macOS/dev: you can simply run `php artisan queue:work` in a terminal; systemd isn’t available on macOS.

### macOS (Herd) development

For local development using Laravel Herd on macOS:

1) Ensure `.env` contains `QUEUE_CONNECTION=database` and migrate the queue tables once:

```
php artisan queue:table
php artisan migrate
```

2) Run the queue worker in a terminal tab:

```
php artisan queue:work --sleep=3 --tries=3 --timeout=120
```

3) Run the scheduler loop in another tab (instead of setting up cron locally):

```
php artisan schedule:work
```

Herd provides PHP and web serving on macOS; you don’t need systemd. Keep these two processes running while developing.

### Environment variables (.env) for queues

Minimal queue-related settings:

```
# Common
QUEUE_CONNECTION=database

# Optional: increase if your feed fetch/summarization can take longer
# These are passed as CLI flags to queue:work; shown here for reference
# WORKER_SLEEP=3
# WORKER_TRIES=3
# WORKER_TIMEOUT=120
```

Recommended worker flags
- Ubuntu (systemd ExecStart): `queue:work --sleep=3 --tries=3 --timeout=120`
- macOS dev (terminal): `queue:work --sleep=3 --tries=3 --timeout=120`

Tune `--timeout` based on the slowest expected job (e.g., long feeds); avoid setting it too high to keep stuck jobs from blocking the worker.

## API (Phase 1)

The application now exposes a versioned read-only JSON API plus an on‑demand summary endpoint under `/api/v1` (all routes require authentication via Sanctum personal access tokens):

Endpoints (GET unless noted):

* `/api/v1/posts` – Paginated list of posts (default page size 20)
	* Filters (use JSON:API style bracket params):
		* `filter[read]=0|1`
		* `filter[source]={source_id}`
		* `filter[category]={category_id}`
	* Includes: `include=source,category` (comma separated)
	* Sorting: `sort=-posted_at` (default newest first) or `sort=posted_at`
	* Pagination: `page` (page number, starting at 1) and `page.size` (1–200)
* `/api/v1/posts/{id}` – Single post, supports `include=source,category`
* `/api/v1/posts/{id}/summary` (POST) – Generate a short HTML summary (see below)
* `/api/v1/sources` – List sources, optional `include=category`
* `/api/v1/sources/{id}` – Single source (future expansion)
* `/api/v1/categories` – List categories
* `/api/v1/categories/{id}` – Single category (future expansion)

Response Shape (examples):

Posts list returns:
```
{
	"data": [ { post fields ... } ],
	"meta": { "total": n, "current_page": 1, "last_page": n, "per_page": 20 }
}
```

Summary endpoint returns:
```
{
	"data": {
		"post_id": 123,
		"sentences": 2,
		"summary": "<p>Sentence one.</p><p>Sentence two.</p>"
	}
}
```

Errors use standard HTTP status codes (401 unauthenticated, 404, 422 validation, 502 upstream summary failure).

## Authentication (Sanctum Personal Access Tokens)

All `/api/v1/*` routes are protected by `auth:sanctum`. For manual usage you create a personal access token and send it as a Bearer token.

Create (Tinker):
```
php artisan tinker
>>> use App\Models\User;
>>> $user = User::first(); // or create a dedicated API user
>>> $token = $user->createToken('dev')->plainTextToken;
>>> $token
"1|XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"
```
Copy the full string (including the leading numeric id before the pipe). It is only shown once.

Request example (curl):
```
curl -H "Accept: application/json" -H "Authorization: Bearer 1|XXXX..." http://infraread.test/api/v1/posts
```

Revoke a token by deleting it from `personal_access_tokens` (e.g. `$user->tokens()->where('name','dev')->delete();`).

## On‑Demand Summaries

Route: `POST /api/v1/posts/{id}/summary`

Body (JSON, optional):
```
{ "sentences": 2 }
```
If `sentences` omitted a sensible default is used. A per-minute rate limit is enforced (configured via the `summaries` rate limiter in `RouteServiceProvider` / `RateLimiter::for('summaries', ...)`). Failures from the upstream summarizer respond with `502` and `{ "error": "..." }`.

## Temporary API Tester UI

Path: `/api-tester` (behind normal web auth session). Paste a personal access token into the Auth Token box; subsequent in-page fetches add the `Authorization: Bearer` header. This page is purely a development aid and should be removed or protected further for production deployments.

## Roadmap / Next Ideas

* Phase 2: Mutations (mark read/unread, mark batch, save for later).
* Token management UI (issue / revoke without Tinker).
* Service layer refactor for summaries (decouple from `Post` model).
* Pagination / filtering metadata alignment with JSON:API (links, first/last URLs).
* Additional includes (e.g. media, category on source detail).

---

_These API + auth notes document the August 2025 milestone adding Phase 1 read-only endpoints, Sanctum integration, and summaries._

## License

M.I.T. do anything you want with the code as long as you provide attribution back and you and don’t hold me liable

## Contact or Questions

Find me on [Twitter](https://twitter.com/beirutspring)

## Built on top of

[Laravel](laravel.com), [VueJs](vuejs.org), [Tailwind Css](https://tailwindcss.com), [Will Vincent Feeds](https://github.com/willvincent/feeds) which 
builds on top of [SimplePie](http://simplepie.org/)
