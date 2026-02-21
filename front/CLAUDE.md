# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

See `../CLAUDE.md` for full-stack project context (architecture, backend, auth system).

## Development Commands

Use yarn, not npm:
- `yarn install` — Install dependencies
- `yarn dev` — Start dev server (port 8080)
- `yarn build` — Production build
- `yarn lint` — Lint and fix files

## Architecture

Vue 3 Composition API application with cookie-based authentication (HttpOnly cookies, no token storage in frontend).

### Key Patterns

**Pinia** for state management (`src/stores/`): `auth.js`, `layout.js`, `notification.js`. No mutations — just state and actions.

**Service Layer** (`src/services/`): Entity services extend `BaseService.js` which provides standard CRUD methods. All HTTP calls go through `src/http/index.js` (Axios with `withCredentials: true`).

**Composables** (`src/composables/`): `useCrud.js` for CRUD state management, `messages.js` for notifications, `setSessions.js` for localStorage filter persistence.

**Base Components** (`src/components/base/`): `Crud.vue`, `TableLists.vue`, `ModalForm.vue`, `Filter.vue`, `Actions.vue` — compose together for standard CRUD pages.

**Router** (`src/router/index.js`): Auth guards check `authStore.loggedIn`. Protected routes use `meta: { authRequired: true }`.

**Permission directive**: `v-permission` controls element visibility based on user permissions from auth store.

**Path alias**: `@` = `src/`
