# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Libertas — a full-stack admin/dashboard system built with Laravel 12 (API) and Vue 3 (SPA). Features user management, goals tracking, audit logging, notifications, reports with PDF/Excel exports, and role-based permissions. The project is written primarily in Brazilian Portuguese (UI text, comments, variable names in some places).

## Repository Structure

```
api/     — Laravel 12 backend (PHP 8.2+, Sanctum auth, MySQL)
front/   — Vue 3 SPA (Composition API, Pinia, Bootstrap 5, Vite)
```

## Development Commands

### Full-Stack (recommended — from `api/` directory)

```bash
composer dev          # Concurrent: Laravel server (:8000) + queue listener + log streaming (pail) + Vite
```

### Backend Only (`api/`)

```bash
composer install
php artisan serve                     # Start server on :8000
php artisan migrate                   # Run migrations
php artisan db:seed                   # Seed database
php artisan test                      # Run all PHPUnit tests
php artisan test --filter=TestName    # Run single test
vendor/bin/pint                       # Fix code style (Laravel Pint)
php artisan queue:listen --tries=1    # Process queued jobs
```

### Frontend Only (`front/`)

```bash
yarn install          # Use yarn, not npm
yarn dev              # Dev server on :8080 (proxies API to :8000)
yarn build            # Production build
yarn lint             # ESLint fix
```

## Architecture

### Backend — Repository-Service-Controller Pattern

All entities follow this layered pattern:

1. **Controller** (`app/Http/Controllers/`) — extends abstract `Controller` which provides standard CRUD actions (index, store, show, update, destroy, bulkDelete, bulkChangeActive, changeActive). Controllers receive a `BaseService` and `FormRequest` via constructor injection.

2. **Service** (`app/Services/`) — extends `BaseService` which provides `get()` with automatic filtering/pagination/sorting based on request parameters, plus standard `create()`, `update()`, `delete()`, `bulkDelete()`, `bulkChangeActive()`. The `get()` method auto-applies filters by matching request parameters to model fillable columns.

3. **Repository** (`app/Repositories/`) — extends `BaseRepository` which wraps Eloquent for data access.

4. **FormRequest** (`app/Http/Requests/`) — validation rules with `applyTransformations()` for data preprocessing. The `rules($id)` method receives the record ID for conditional validation (e.g., unique except self).

**Important**: Resource IDs in API URLs are base64-encoded. The base Controller decodes them automatically in `update()`, `show()`, `destroy()`, and `changeActive()`.

### Authentication — Cookie-Based with Sanctum

- Login creates two Sanctum tokens: access (15min) and refresh (7 days), stored as HttpOnly cookies
- `CookieToTokenMiddleware` converts cookies to Bearer tokens on each request
- `RefreshTokenMiddleware` auto-refreshes expired access tokens
- No tokens stored in frontend JavaScript — frontend uses `withCredentials: true`
- Middleware chain: `cookie.to.token` → `auth:sanctum` → `is.active` → `permission`

### Frontend — Service-Composable-Component Pattern

- **Services** (`src/services/`) extend `BaseService.js` for standard CRUD HTTP calls
- **Composables** (`src/composables/`) — `useCrud.js` manages CRUD state, `setSessions.js` persists filters in localStorage
- **Base Components** (`src/components/base/`) — `Crud.vue`, `TableLists.vue`, `ModalForm.vue`, `Filter.vue` compose together to build CRUD pages
- **Stores** (`src/stores/`) — Pinia stores for auth, layout, notifications (no mutations, just state + actions)
- **Router** (`src/router/`) — auth guards check `authStore.loggedIn`, protected routes use `meta: { authRequired: true }`
- **Permission directive** — `v-permission` controls element visibility based on user permissions
- **Path alias** — `@` maps to `src/`

### Audit System

- `Auditable` trait and `UserObserver` auto-log model changes (old/new values as JSON diffs)
- `AuditService` logs authentication events (login, logout, failed attempts)
- Captures IP address, user agent, action type, model type

### Permission System

- Hierarchical parent-child permissions in `permissions` table
- `CheckPermission` middleware validates route-level access
- `v-permission` Vue directive for frontend element-level control

## Conventions — Follow These Patterns When Writing New Code

### Backend: Services / Repositories

Every new entity must follow the full stack: **Model → Repository → Service → FormRequest → Controller**.

**Creating a new entity (e.g., `Product`):**

1. **Model** (`app/Models/Product.php`) — define `$fillable`, relationships, and `SoftDeletes` if needed.

2. **Repository** (`app/Repositories/ProductRepository.php`) — extend `BaseRepository`, inject the Model. Only add methods for queries that go beyond what `BaseRepository` already provides (e.g., custom joins, aggregations). Simple CRUD is inherited automatically.
   ```php
   class ProductRepository extends BaseRepository
   {
       public function __construct(Product $model)
       {
           parent::__construct($model);
       }
       // Custom queries only — don't reimplement find(), create(), update(), delete()
   }
   ```

3. **Service** (`app/Services/ProductService.php`) — extend `BaseService`, inject the Repository. Place all **business logic** here (data preparation, validations beyond FormRequest, relationships sync, file uploads). Override `create()` / `update()` only when you need custom logic; the base methods work for simple entities.
   ```php
   class ProductService extends BaseService
   {
       public function __construct(ProductRepository $repository)
       {
           parent::__construct($repository);
       }
       // Business logic — e.g., prepareData, sync relationships, dispatch jobs
   }
   ```

4. **FormRequest** (`app/Http/Requests/ProductRequest.php`) — define `rules($id)`, `messages()`, `attributes()`, and `applyTransformations($data)` for data cleanup before validation.

5. **Controller** (`app/Http/Controllers/Api/ProductController.php`) — extend `Controller`, inject Service and FormRequest. The base Controller already provides `index`, `store`, `show`, `update`, `destroy`, `bulkDelete`, `bulkChangeActive`, `changeActive`. Override only when you need to customize columns, add extra logic, or have non-standard endpoints.
   ```php
   class ProductController extends Controller
   {
       public function __construct(ProductService $service)
       {
           parent::__construct($service, new ProductRequest);
       }
       // Override index() only if you need specific columns or custom queries
   }
   ```

6. **Routes** (`routes/api.php`) — add inside the permission-protected group using `apiResource`, plus custom action routes (bulk-delete, bulk-change-active, change-active) before the resource route.

**Key rules:**
- Controllers must be thin — no business logic, no direct Eloquent calls. Delegate everything to the Service.
- Services must not contain raw SQL or query builder logic — delegate data access to the Repository.
- Repositories must not contain business logic — only data access and queries.
- IDs in URLs are base64-encoded; the base Controller handles decoding.

### Frontend: Components, Services, and Composables

Keep components **clean and declarative** — all logic goes into services and composables.

**Creating a new entity CRUD (e.g., `Product`):**

1. **Service** (`src/services/ProductService.js`) — extend `BaseService`. Define the entity's configuration as constants at the top of the file, then expose them through getter methods:
   ```js
   const PRODUCT_FORM = { id: 0, name: '', price: '', active: true };
   const PRODUCT_FILTER = [{ name: 'name', placeholder: 'Nome', col: '3', type: 'text' }];
   const PRODUCT_TABLE = [{ column: 'check' }, { order: 'name', column: 'Nome' }, { column: 'Ações' }];
   const PRODUCT_KEYS = ['check', 'name', 'actions'];
   const PRODUCT_ACTIONS = ['page', 'delete'];

   export default class ProductService extends BaseService {
       constructor() { super('products'); }
       getDefaultFormData() { return JSON.parse(JSON.stringify(PRODUCT_FORM)); }
       getFilterConfig() { return JSON.parse(JSON.stringify(PRODUCT_FILTER)); }
       getTableConfig() { return PRODUCT_TABLE; }
       getTableKeys() { return PRODUCT_KEYS; }
       getActionTypes() { return PRODUCT_ACTIONS; }
       // Entity-specific API calls only — CRUD is inherited from BaseService
   }
   ```

2. **Composable** — use `useCrud(service, defaultFormData, sessionName, options)` from `src/composables/useCrud.js` to manage CRUD state (formData, save, delete, changeStatus). Use options callbacks (`validateForm`, `prepareData`, `afterSave`, `afterDelete`) for entity-specific logic. Create new composables in `src/composables/` for any reusable logic that doesn't belong in a service.

3. **View Component** — compose using the base components: `Crud.vue` (full CRUD layout), `TableLists.vue` (data table), `ModalForm.vue` (form modal), `Filter.vue` (filtering), `Actions.vue` (row actions). The component should mostly be template + wiring, not logic.

**Key rules:**
- **No business logic in components** — move it to composables or services. Components should only handle template rendering and event wiring.
- **All HTTP calls go through services** — never call `axios`/`http` directly from components or composables. Entity services extend `BaseService` which provides `index()`, `getById()`, `save()`, `delete()`, `bulkDelete()`, `bulkChangeActive()`.
- **Reusable logic goes in composables** (`src/composables/`) — date formatting (`manageDates.js`), input masks (`masks.js`), toast notifications (`messages.js`), spinners (`useGlobalSpinner.js`, `useRegionalSpinner.js`, `spinners.js`), filter session persistence (`setSessions.js`), utility functions (`functions.js`).
- **Global state in Pinia stores** (`src/stores/`) — only for truly global concerns (auth, layout, notifications). Don't create a store for entity-level CRUD state; use `useCrud` composable instead.
- **Use `BaseService` configuration pattern** — each entity service defines its form defaults, filter config, table config, table keys, and action types. This drives the base components generically.
- **Use `@` path alias** — always import with `@/services/...`, `@/composables/...`, etc.

## API Routes

All routes prefixed with `/api/v1/`. Public: login, forgot-password, recover-password, enums. Protected routes require the full middleware chain. Permission-protected routes use `apiResource` for standard CRUD plus custom actions (bulk-delete, bulk-change-active, change-active).

## Key Dependencies

**Backend**: laravel/sanctum (auth tokens), barryvdh/laravel-dompdf (PDF export), phpoffice/phpspreadsheet (Excel export), laravel/pint (code style), laravel/pail (log streaming)

**Frontend**: vue 3, vue-router 4, pinia 3, axios, bootstrap 5, bootstrap-vue-3, apexcharts/vue3-apexcharts, sweetalert2, notivue, date-fns, flatpickr, maska (input masks)
