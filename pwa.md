## PWA plan for this Laravel + Vue 2 frontend

### 1) Confirm scope and entry points
- Identify the JS entry that boots `App.vue` (likely `resources/js/app.js`) and the Blade layout that serves it (often `resources/views/layouts/app.blade.php` or similar).
- Decide the PWA scope (`/`) and start URL (likely `/`).
- Confirm which routes must work offline (home list, last-read posts, settings) and which can be online-only (admin, auth).

### 2) Add a Web App Manifest
- Create `public/manifest.webmanifest` with:
  - `name`, `short_name`, `start_url`, `display`, `background_color`, `theme_color`, `icons`.
- Generate app icons and place them under `public/icons/` (at least 192x192 and 512x512).
- Link the manifest and theme color in the main Blade layout:
  - `<link rel="manifest" href="/manifest.webmanifest">`
  - `<meta name="theme-color" content="#...">`

### 3) Add a Service Worker (SW)
Two viable paths (choose one):

**A. Manual SW (no new dependencies)**
- Add `public/service-worker.js` and implement:
  - Precache critical shell assets (CSS/JS bundles, logo).
  - Runtime caching for API calls (`/api/...`) with a stale-while-revalidate strategy.
  - Offline fallback for HTML (optional).
- Register the SW from the JS entry (e.g. in `resources/js/app.js`) once `window.load` fires.

**B. Workbox build (requires dependency approval)**
- Add Workbox via `npm` and integrate with `webpack.mix.js` for `injectManifest`.
- Create `resources/js/service-worker.js` with Workbox routes and precache list.
- Build to `public/service-worker.js` on production builds.

### 4) Handle updates and UX
- Add a simple “New version available” toast when a new SW is installed.
- Decide whether to auto-refresh or prompt the user.
- Use `wire:offline`-like UX patterns in Vue (simple banner) when offline.

### 5) Verify HTTPS, caching, and installability
- Confirm app is served via HTTPS (Laravel Herd already provides this).
- Validate PWA installability in browser DevTools (Application panel).
- Ensure cache headers are compatible with SW caching (avoid double-caching pitfalls).

### 6) Tests and checks
- Add a lightweight JS test (if tests exist) or a feature test that asserts manifest and SW routes return 200.
- Run minimal tests: `php artisan test --compact --filter=Pwa` (or a specific new test file).

### 7) Rollout and monitoring
- Deploy, then verify:
  - `manifest.webmanifest` is accessible.
  - `service-worker.js` is served with the correct scope.
  - Cache updates on new deploys.

---
If you want, I can proceed with option A (manual SW, no new dependencies) or option B (Workbox, best DX) and implement it.
