# Auth-Cookies Frontend (Vue 3)

A Vue 3 frontend application with cookie-based authentication, built using the Composition API and featuring a complete CRUD system with reusable components.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Architecture](#architecture)
- [Authentication System](#authentication-system)
- [Technical Decisions](#technical-decisions)
- [Key Components](#key-components)
- [State Management](#state-management)
- [Development Commands](#development-commands)

## Requirements

- Node.js 18+
- Yarn (preferred over npm)

## Installation

```bash
# Navigate to front directory
cd front

# Copy environment configuration
cd src
cp env.example.js env.js
cd ..

# Install dependencies (use yarn, not npm)
yarn install

# Start development server
yarn dev
```

### Environment Configuration

Edit `src/env.js` with your settings:

```javascript
export default {
  API_URL: 'http://localhost:8000/api',
  APP_NAME: 'Auth Cookies',
  // Add other environment variables as needed
}
```

## Architecture

### Directory Structure

```
src/
├── assets/
│   └── scss/           # SCSS styles with corporate theme
├── components/
│   ├── base/           # Reusable base components
│   │   ├── Crud.vue    # Complete CRUD component
│   │   ├── TableLists.vue
│   │   ├── ModalForm.vue
│   │   └── ...
│   └── widgets/        # Dashboard widgets
├── composables/        # Vue 3 composables
│   ├── cruds.js        # CRUD operation helpers
│   ├── messages.js     # Notification helpers
│   └── functions.js    # Utility functions
├── http/
│   └── index.js        # Axios configuration with interceptors
├── router/
│   └── index.js        # Vue Router with auth guards
├── services/           # API service classes
│   ├── BaseService.js  # Base service with CRUD methods
│   ├── AuthService.js
│   └── UserService.js
├── stores/             # Pinia stores
│   ├── auth.js         # Authentication state
│   └── layout.js       # Layout/UI state
└── views/              # Page components
    ├── auth/           # Login, Register, Password Reset
    └── users/          # User management pages
```

### Design Patterns

#### Composables Pattern
Vue 3 Composition API composables for reusable logic:
- `useCrud()` - Complete CRUD operations with form handling
- `useMessages()` - Notification system integration
- `useFunctions()` - Common utility functions

#### Service Layer Pattern
API interactions are encapsulated in service classes:
- Extends `BaseService` for consistent CRUD operations
- Handles request/response transformation
- Centralizes endpoint definitions

#### Base Components Pattern
Reusable components for common UI patterns:
- `Crud.vue` - Full CRUD interface (table + modal form)
- `TableLists.vue` - Configurable data tables
- `ModalForm.vue` - Form modals with validation

## Authentication System

### How It Works

The frontend relies entirely on HttpOnly cookies set by the backend:

```
1. User submits login form
              ↓
2. AuthService.login() sends credentials to API
              ↓
3. API validates and sets HttpOnly cookies (access_token, refresh_token)
              ↓
4. Frontend receives user data (no token handling needed!)
              ↓
5. Subsequent requests automatically include cookies
              ↓
6. Token refresh happens transparently on backend
```

### Why No Token Storage in Frontend?

1. **Security**: HttpOnly cookies cannot be accessed by JavaScript (XSS protection)
2. **Simplicity**: No need to manage token storage, refresh logic, or attach headers
3. **Automatic**: Browser handles cookie transmission automatically

### Authentication Flow Implementation

#### Login (`src/services/AuthService.js`)
```javascript
async login(credentials) {
  const response = await http.post('/auth/login', credentials)
  // Cookies are automatically set by browser from response
  return response.data.user
}
```

#### Axios Configuration (`src/http/index.js`)
```javascript
const http = axios.create({
  baseURL: env.API_URL,
  withCredentials: true, // Essential! Sends cookies with requests
})
```

#### Route Guards (`src/router/index.js`)
```javascript
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else {
    next()
  }
})
```

## Technical Decisions

### Why Vue 3 Composition API?

- **Better TypeScript support**: Improved type inference
- **Code organization**: Logic can be grouped by feature, not option type
- **Reusability**: Composables enable clean logic extraction
- **Performance**: Better tree-shaking and bundle optimization

### Why Pinia over Vuex?

- **Simpler API**: No mutations, just state and actions
- **TypeScript native**: Built with TypeScript from the ground up
- **Devtools support**: Full Vue Devtools integration
- **Modular by design**: Each store is independent

### Why Yarn over npm?

- **Deterministic**: Lockfile ensures consistent installs
- **Faster**: Parallel installation and caching
- **Workspaces**: Better monorepo support

### Why Bootstrap Vue 3?

- **Rapid development**: Pre-built components
- **Consistency**: Uniform styling across the application
- **Customizable**: SCSS variables for theming
- **Accessibility**: Built-in ARIA support

### Why Axios?

- **Interceptors**: Easy request/response transformation
- **Cancellation**: Request cancellation support
- **Automatic transforms**: JSON parsing, error handling
- **Browser + Node**: Universal support

## Key Components

### Crud.vue (`src/components/base/Crud.vue`)

Complete CRUD interface combining table and form:

```vue
<template>
  <Crud
    :service="userService"
    :columns="columns"
    :form-fields="formFields"
    title="Users"
  />
</template>
```

Features:
- Automatic pagination
- Search and filtering
- Create/Edit/Delete actions
- Form validation
- Loading states

### useCrud Composable (`src/composables/cruds.js`)

Provides CRUD operations for any entity:

```javascript
const {
  items,
  loading,
  pagination,
  fetchItems,
  createItem,
  updateItem,
  deleteItem,
} = useCrud(userService)
```

### BaseService (`src/services/BaseService.js`)

Base class for API services:

```javascript
class UserService extends BaseService {
  constructor() {
    super('/users')
  }

  // Custom methods
  async changePassword(id, data) {
    return this.http.post(`${this.endpoint}/${id}/change-password`, data)
  }
}
```

## State Management

### Auth Store (`src/stores/auth.js`)

Manages authentication state:

```javascript
export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false,
  }),

  actions: {
    async login(credentials) {
      const user = await authService.login(credentials)
      this.user = user
      this.isAuthenticated = true
    },

    async logout() {
      await authService.logout()
      this.user = null
      this.isAuthenticated = false
    },

    async checkAuth() {
      try {
        const user = await authService.me()
        this.user = user
        this.isAuthenticated = true
      } catch {
        this.isAuthenticated = false
      }
    },
  },
})
```

### Layout Store (`src/stores/layout.js`)

Manages UI state (sidebar, theme, etc.):

```javascript
export const useLayoutStore = defineStore('layout', {
  state: () => ({
    sidebarCollapsed: false,
    theme: 'light',
  }),

  actions: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed
    },
  },
})
```

## HTTP Configuration

### Axios Setup (`src/http/index.js`)

```javascript
import axios from 'axios'
import env from '@/env'

const http = axios.create({
  baseURL: env.API_URL,
  withCredentials: true, // Required for cookies!
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Response interceptor for error handling
http.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Redirect to login
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default http
```

## Development Commands

```bash
# Install dependencies
yarn install

# Start development server with hot-reload
yarn dev

# Build for production
yarn build

# Preview production build
yarn preview

# Lint and fix files
yarn lint

# Type check (if using TypeScript)
yarn type-check
```

## Project Configuration

### Vite Configuration (`vite.config.js`)

Key settings:
- Path aliases (`@` for `src/`)
- Proxy configuration for API requests (optional)
- Build optimization settings

### ESLint + Prettier

Code quality tools configured for:
- Vue 3 specific rules
- Composition API best practices
- Consistent code formatting

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

Note: HttpOnly cookies require HTTPS in production for the `Secure` flag.

## Troubleshooting

### Cookies not being sent

1. Ensure `withCredentials: true` in Axios config
2. Check CORS configuration on backend
3. Verify `SANCTUM_STATEFUL_DOMAINS` includes frontend URL

### 401 errors after login

1. Check if cookies are being set (DevTools > Application > Cookies)
2. Verify backend `SESSION_DOMAIN` matches frontend domain
3. Ensure frontend and backend are on same domain (or properly configured for cross-origin)

### CORS errors

Backend must allow:
- Origin: Your frontend URL
- Credentials: true
- Headers: Content-Type, Accept, X-Requested-With

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
