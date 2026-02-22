# Frontend — Vue 3 SPA

Vue 3 single-page application with cookie-based authentication, built using the Composition API. Features a complete CRUD system with reusable base components, payment checkout, and role-based permission control.

Aplicação single-page em Vue 3 com autenticação baseada em cookies, construída com a Composition API. Inclui um sistema CRUD completo com componentes base reutilizáveis, checkout de pagamento e controle de permissões por papel.

---

## Requirements / Pré-requisitos

- Node.js 18+
- Yarn (use yarn, not npm / use yarn, não npm)

## Installation / Instalação

```bash
cd front
cp src/env.example.js src/env.js    # Configure environment / Configurar ambiente
yarn install
```

### Environment Configuration / Configuração de Ambiente

Edit `src/env.js` with your settings / Edite `src/env.js` com suas configurações:

```javascript
export default {
  API_URL: 'http://localhost:8000/api',
  APP_NAME: 'Libertas',
}
```

## Commands / Comandos

```bash
yarn dev       # Dev server on :8080 (proxies API to :8000)
               # Servidor dev em :8080 (proxy para API :8000)
yarn build     # Production build / Build de produção
yarn preview   # Preview production build / Pré-visualizar build
yarn lint      # ESLint fix / Corrigir ESLint
```

## Architecture / Arquitetura

### Directory Structure / Estrutura de Diretórios

```
src/
├── assets/
│   └── scss/              # SCSS styles with corporate theme
│                          # Estilos SCSS com tema corporativo
├── components/
│   ├── base/              # Reusable CRUD components / Componentes CRUD reutilizáveis
│   │   ├── Crud.vue       # Full CRUD interface / Interface CRUD completa
│   │   ├── TableLists.vue # Configurable data tables / Tabelas configuráveis
│   │   ├── ModalForm.vue  # Form modals / Modais de formulário
│   │   ├── Filter.vue     # Filtering component / Componente de filtro
│   │   ├── Actions.vue    # Row actions / Ações de linha
│   │   └── Pagination.vue # Pagination / Paginação
│   ├── dashboard/         # Dashboard widgets
│   └── layouts/           # Layout components / Componentes de layout
├── composables/           # Vue 3 composables
│   ├── cruds.js           # CRUD state management / Gerenciamento de estado CRUD
│   ├── messages.js        # Notification helpers / Helpers de notificação
│   ├── functions.js       # Utility functions / Funções utilitárias
│   ├── masks.js           # Input mask patterns / Máscaras de input
│   └── setSessions.js     # Filter persistence (localStorage)
│                          # Persistência de filtros (localStorage)
├── directives/
│   └── permission.js      # v-permission directive / Diretiva v-permission
├── http/
│   └── index.js           # Axios config (withCredentials: true)
├── router/
│   ├── index.js           # Vue Router with auth guards / Com guardas de autenticação
│   └── routes.js          # Route definitions / Definições de rotas
├── services/              # API service classes / Classes de serviço da API
│   ├── BaseService.js     # Base class with CRUD methods / Classe base com métodos CRUD
│   ├── AuthService.js     # Authentication / Autenticação
│   ├── ProductService.js  # Products / Produtos
│   ├── OrderService.js    # Orders / Pedidos
│   └── UserService.js     # Users / Usuários
├── stores/                # Pinia stores
│   ├── auth.js            # Auth state / Estado de autenticação
│   ├── layout.js          # Layout/theme state / Estado de layout/tema
│   └── notification.js    # Notification state / Estado de notificações
└── views/                 # Page components / Componentes de página
    ├── account/           # Login, ForgotPassword, Logout
    ├── dashboard/         # Dashboard
    ├── products/          # Checkout
    ├── profile/           # MyProfile
    └── users/             # User management / Gestão de usuários
```

### Design Patterns / Padrões de Projeto

#### Service Layer / Camada de Serviço

All HTTP calls go through services extending `BaseService`. Never call axios directly from components.

Todas as chamadas HTTP passam por services que estendem `BaseService`. Nunca chame axios diretamente de componentes.

```javascript
class ProductService extends BaseService {
  constructor() { super('products') }
  // Inherits: index(), getById(), save(), delete(), bulkDelete(), bulkChangeActive()
  // Herda: index(), getById(), save(), delete(), bulkDelete(), bulkChangeActive()
}
```

#### Composables Pattern / Padrão de Composables

Reusable logic extracted into composables / Lógica reutilizável extraída em composables:

- `useCrud(service, defaultFormData, sessionName)` — CRUD state, save, delete, pagination
- `useMessages()` — `notifySuccess()`, `notifyError()`, SweetAlert confirmations
- `setSessions()` — persist filters in localStorage between page visits / persistir filtros no localStorage

#### Base Components / Componentes Base

Compose together for standard CRUD pages / Compostos juntos para páginas CRUD padrão:

```vue
<template>
  <Crud title="Products" :service="productService">
    <template #table>
      <TableLists :columns="columns" :keys="keys" :items="items">
        <template #actions="{ item }">
          <Actions :item="item" :types="actionTypes" />
        </template>
      </TableLists>
    </template>
    <template #modal>
      <ModalForm :formData="formData" @save="save" />
    </template>
  </Crud>
</template>
```

## Authentication System / Sistema de Autenticação

The frontend relies entirely on HttpOnly cookies set by the backend. No tokens are stored in JavaScript.

O frontend depende inteiramente de cookies HttpOnly definidos pelo backend. Nenhum token é armazenado no JavaScript.

```
1. User submits login / Usuário envia login
              ↓
2. API validates + sets HttpOnly cookies (access 15min + refresh 7d)
   API valida + define cookies HttpOnly
              ↓
3. Browser sends cookies automatically on every request
   Navegador envia cookies automaticamente em cada requisição
              ↓
4. Token refresh is transparent (backend middleware)
   Refresh de token é transparente (middleware do backend)
```

### Key configuration / Configuração chave:

```javascript
// src/http/index.js
const http = axios.create({
  baseURL: env.API_URL,
  withCredentials: true,  // Required for cookies! / Obrigatório para cookies!
})
```

### Route Guards / Guardas de Rota

```javascript
// Protected routes use / Rotas protegidas usam:
meta: { authRequired: true }

// Router checks / O router verifica:
if (to.meta.authRequired && !authStore.loggedIn) → redirect to /login
```

### Permission Directive / Diretiva de Permissão

```vue
<!-- Element only visible if user has this permission -->
<!-- Elemento só visível se o usuário tiver essa permissão -->
<button v-permission="'users.create'">New User / Novo Usuário</button>
```

## State Management / Gerenciamento de Estado

Pinia stores for global concerns. No mutations — just state + actions.

Pinia stores para estado global. Sem mutations — apenas state + actions.

| Store          | Purpose / Propósito                                                |
|----------------|--------------------------------------------------------------------|
| `auth.js`      | Current user, login state, enums / Usuário atual, estado de login  |
| `layout.js`    | Sidebar, theme, topbar / Barra lateral, tema, topbar               |
| `notification` | Notification count and list / Contagem e lista de notificações     |

> Entity-level CRUD state uses `useCrud` composable, not stores.
> Estado CRUD de entidades usa o composable `useCrud`, não stores.

## Key Dependencies / Dependências Principais

| Package                  | Purpose / Propósito                                      |
|--------------------------|----------------------------------------------------------|
| `vue` 3                  | Framework                                                |
| `vue-router` 4           | Routing / Roteamento                                     |
| `pinia` 3                | State management / Gerenciamento de estado               |
| `axios`                  | HTTP client                                              |
| `bootstrap` 5            | CSS framework                                            |
| `bootstrap-vue-3`        | Vue Bootstrap components / Componentes Bootstrap Vue     |
| `apexcharts`             | Charts / Gráficos                                        |
| `sweetalert2`            | Confirmation dialogs / Diálogos de confirmação           |
| `notivue`                | Toast notifications / Notificações toast                 |
| `flatpickr`              | Date picker / Seletor de data                            |
| `maska`                  | Input masks / Máscaras de input                          |
| `date-fns`               | Date utilities / Utilitários de data                     |

## Vite Configuration / Configuração do Vite

Key settings from `vite.config.js` / Configurações principais:

- **Port / Porta**: 8080
- **API Proxy**: `/api` → `http://localhost:8000` (dev only / somente em dev)
- **Path alias**: `@` → `src/`
- **Build chunks**: vendor (vue, router, pinia, axios), bootstrap, utils

## Troubleshooting / Solução de Problemas

### Cookies not being sent / Cookies não estão sendo enviados

1. Ensure `withCredentials: true` in Axios config / Verifique `withCredentials: true` na config do Axios
2. Check CORS configuration on backend / Verifique a configuração de CORS no backend
3. Verify `CORS_ALLOWED_ORIGINS` includes frontend URL / Verifique se inclui a URL do frontend

### 401 errors after login / Erros 401 após login

1. Check if cookies are set (DevTools > Application > Cookies) / Verifique se os cookies foram definidos
2. Verify backend `SESSION_DOMAIN` matches frontend domain / Verifique se o domínio está correto
3. Ensure same domain or proper CORS config / Mesmo domínio ou CORS configurado

### CORS errors / Erros de CORS

Backend must allow / O backend deve permitir:
- Origin: frontend URL
- Credentials: true
- Headers: Content-Type, Accept, X-Requested-With

## License / Licença

MIT
