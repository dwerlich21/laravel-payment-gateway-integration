<template>
    <Main>
        <PageHeader :title="titleHeader || titlePluralize"/>

        <!-- Slot para conteudo antes do card principal (ex: stats) -->
        <slot name="before-card"></slot>

        <BRow>
            <BCol xxl="12" class="mb-3">


                <BCard
                    no-body
                    class="mb-3"
                >
                    <!-- Componente de filtro -->
                    <Filter
                        :session="session"
                        :title="title"
                        :url="url"
                        :filter="filter"
                        :title-pluralize="titlePluralize"
                        :endpoint="endpoint"
                        :views="views"
                        :hide-add-button="hideAddButton"
                        @new-view="changeViewType"
                        @filter-applied="loadList"
                    />

                    <!-- Barra de ações em massa -->
                    <BulkActions
                        v-if="bulkActions.enabled"
                        :selected-count="selectedIds.length"
                        :show-delete-action="bulkActions.showDelete"
                        :show-active-actions="bulkActions.showActive"
                        :show-status-actions="bulkActions.showStatus"
                        :status-options="bulkActions.statusOptions"
                        @delete="handleBulkDelete"
                        @change-active="handleBulkChangeActive"
                        @change-status="handleBulkChangeStatus"
                        @clear-selection="clearSelection"
                    />

                    <div v-if="viewType === 'table'">
                        <!-- Visualização em tabela usando BTable -->
                        <BCardBody class="position-relative p-0">
                            <!-- Skeleton Loading -->
                            <div
                                v-if="loading && listAll.length === 0"
                                class="skeleton-container"
                            >
                                <table class="table table-striped mb-0">
                                    <thead>
                                    <tr>
                                        <th
                                            v-for="field in fields"
                                            :key="'skeleton-th-' + field.key"
                                            class="skeleton-th"
                                        >
                                            <div
                                                class="skeleton skeleton-text"
                                                style="width: 80%;"
                                            />
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr
                                        v-for="i in 5"
                                        :key="'skeleton-row-' + i"
                                        class="skeleton-row"
                                        :style="{ animationDelay: `${i * 0.1}s` }"
                                    >
                                        <td
                                            v-for="(field, j) in fields"
                                            :key="'skeleton-td-' + i + '-' + j"
                                        >
                                            <div
                                                class="skeleton skeleton-text"
                                                :style="{ width: getSkeletonWidth(field.key) }"
                                            />
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Tabela com dados -->
                            <TransitionGroup
                                v-else
                                name="table-rows"
                                tag="div"
                                class="table-sticky-container"
                            >
                                <BTable
                                    id="crud-table"
                                    key="data-table"
                                    :items="listAll"
                                    :fields="fields"
                                    striped
                                    hover
                                    show-empty
                                    :class="['align-middle', 'mb-0', 'p-0', { 'table-loading': loading }]"
                                    :tbody-tr-class="getRowClass"
                                >
                                    <!-- Empty state customizado -->
                                    <template #empty>
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <i class="ri-inbox-line"/>
                                            </div>
                                            <h5 class="empty-state-title">Nenhum registro encontrado</h5>
                                            <p class="empty-state-text">
                                                Não há dados para exibir no momento.
                                                <br>
                                                Tente ajustar os filtros ou adicione um novo registro.
                                            </p>
                                            <router-link
                                                v-if="canCreate"
                                                :to="`/${url}/cadastrar`"
                                                class="btn btn-primary btn-sm"
                                            >
                                                <i class="ri-add-line me-1"/>
                                                Adicionar {{ title }}
                                            </router-link>
                                        </div>
                                    </template>

                                    <!-- Header customizado com SortableHeader -->
                                    <template
                                        v-for="field in fields"
                                        :key="'head-' + field.key"
                                        #[`head(${field.key})`]="data"
                                    >
                                        <!-- Checkbox para seleção -->
                                        <template v-if="field.key === 'check'">
                                            <input
                                                id="selectAll"
                                                type="checkbox"
                                                class="form-check-input"
                                                @change="selectAll"
                                            >
                                        </template>

                                        <!-- Header com ordenação -->
                                        <template v-else-if="field.sortable">
                                            <SortableHeader
                                                :label="field.label"
                                                :column="field.orderKey"
                                                :sort-by="order_by"
                                                :sort-desc="order === 'desc'"
                                                :loading="loading"
                                                @sort="handleSort"
                                            />
                                        </template>

                                        <!-- Header simples -->
                                        <template v-else>
                                            {{ field.label }}
                                        </template>
                                    </template>

                                    <!-- Celula de checkbox para selecao -->
                                    <template #cell(check)="{ item }">
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            :checked="selectedIds.includes(item.id)"
                                            @change="toggleSelection(item.id)"
                                        >
                                    </template>

                                    <!-- Células customizadas via slots dinâmicos (exceto check que tem template próprio) -->
                                    <template
                                        v-for="key in keys.filter(k => k !== 'check')"
                                        :key="'cell-' + key"
                                        #[`cell(${key})`]="{ item }"
                                    >
                                        <slot
                                            :name="key"
                                            :value="item"
                                        >
                                            {{ getNestedValue(item, key) }}
                                        </slot>
                                    </template>
                                </BTable>
                            </TransitionGroup>

                            <!-- Loading overlay para recarregamento -->
                            <Transition name="fade">
                                <div
                                    v-if="loading && listAll.length > 0"
                                    class="table-loading-overlay"
                                >
                                    <div class="loading-content">
                                        <BSpinner
                                            variant="primary"
                                            style="width: 2.5rem; height: 2.5rem;"
                                        />
                                        <p class="loading-text">Atualizando...</p>
                                    </div>
                                </div>
                            </Transition>
                        </BCardBody>

                        <!-- Footer com paginação -->
                        <BCardFooter class="border-top-0">
                            <div class="align-items-center mt-xl-3 justify-content-between d-lg-flex">
                                <!-- Seletor de registros por página -->
                                <div class="align-items-center d-flex text-muted mb-2 mb-lg-0">
                                    <div class="me-2">
                                        <span>Exibir</span>
                                    </div>
                                    <div class="col-auto">
                                        <BFormSelect
                                            id="limitFilter"
                                            v-model="limit"
                                            :options="perPageOptions"
                                            size="sm"
                                            @update:model-value="setLimit"
                                        />
                                    </div>
                                    <div class="ms-2">
                                        <span>registros</span>
                                    </div>
                                </div>

                                <!-- Info de registros -->
                                <div class="flex-shrink-0 me-lg-auto ms-lg-4 mb-2 mb-lg-0">
                                    <div
                                        v-if="total > 0"
                                        class="text-muted"
                                    >
                                        Exibindo de
                                        <span class="fw-semibold">{{ start }}</span>
                                        a
                                        <span class="fw-semibold">{{ partial }}</span>
                                        de
                                        <span class="fw-semibold">{{ total }}</span>
                                        resultados
                                    </div>
                                    <div
                                        v-else
                                        class="text-muted"
                                    >
                                        Nenhum resultado
                                    </div>
                                </div>

                                <!-- Paginação com BPagination -->
                                <div class="pagination-wrapper">
                                    <BPagination
                                        v-if="pages > 1"
                                        v-model="page"
                                        :total-rows="total"
                                        :per-page="limit"
                                        class="mb-0 gap-1 gap-lg-2"
                                        @update:model-value="handlePageChange"
                                    />
                                </div>
                            </div>
                        </BCardFooter>
                    </div>
                </BCard>

                <!-- Slot para visualização em cards -->
                <slot
                    v-if="viewType === 'card'"
                    name="cards"
                />
            </BCol>
        </BRow>
    </Main>
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {endLoadTable} from "@/composables/functions";
import {setSessions} from "@/composables/setSessions";
import {usePermissions} from "@/utils/permissions.js";

import Main from "@/components/layouts/main.vue";
import PageHeader from "@/components/layouts/page-header.vue";
import Filter from "./Filter.vue";
import SortableHeader from "./SortableHeader.vue";
import BulkActions from "./BulkActions.vue";

const props = defineProps({
    service: {
        required: true,
        type: Object
    },
    session: {
        required: true,
        type: String
    },
    endpoint: {
        required: true,
        type: String
    },
    url: {
        required: true,
        type: String
    },
    title: {
        required: true,
        type: String,
    },
    titleHeader: {
        required: false,
        type: String,
        default: ''
    },
    titlePluralize: {
        required: true,
        type: String
    },
    table: {
        required: true,
        type: Array
    },
    keys: {
        required: true,
        type: Array
    },
    filter: {
        required: true,
        type: Array,
    },
    views: {
        required: false,
        type: Boolean,
        default: false
    },
    hideAddButton: {
        required: false,
        type: Boolean,
        default: false
    },
    bulkActions: {
        required: false,
        type: Object,
        default: () => ({
            enabled: false,
            showDelete: true,
            showActive: true,
            showStatus: false,
            statusOptions: []
        })
    }
});

const emits = defineEmits([
    'update-show-card',
    'load-kanban',
    'bulk-delete',
    'bulk-change-active',
    'bulk-change-status'
]);

// Estado
const loading = ref(false);
const selectedIds = ref([]);
const order = ref(null);
const order_by = ref(null);
const viewType = ref(localStorage.getItem(`views-${props.session}`) || 'table');
const canCreate = ref(false);

const listAll = ref([]);
const start = ref(0);
const partial = ref(0);
const total = ref(0);
const page = ref(1);
const pages = ref(0);
const limit = ref(25);

// Opções de registros por página
const perPageOptions = [
    {value: 25, text: '25'},
    {value: 50, text: '50'},
    {value: 100, text: '100'},
    {value: 250, text: '250'}
];

// Computed: transforma table e keys em fields para BTable
const fields = computed(() => {
    return props.table.map((col, index) => {
        const key = props.keys[index];
        const isSortable = !!col.order;

        return {
            key: key,
            label: col.column,
            sortable: isSortable,
            orderKey: col.order || null,
            thClass: getThClass(col, index),
            tdClass: getTdClass(key, index),
            thStyle: getThStyle(col)
        };
    });
});

// Classes para th
function getThClass(col, index) {
    const classes = ['text-nowrap'];
    const isFirstSpecial = props.table[0]?.column === 'check' || props.table[0]?.column === 'ID';

    if ((index > 0 && !isFirstSpecial) || (index > 1 && isFirstSpecial)) {
        classes.push('text-center');
    }

    return classes.join(' ');
}

// Classes para td
function getTdClass(key, index) {
    const classes = [];
    const isFirstSpecial = props.table[0]?.column === 'check' || props.table[0]?.column === 'ID';

    if ((index > 0 && !isFirstSpecial) || (index > 1 && isFirstSpecial)) {
        classes.push('text-center');
    }

    if (key === 'actions') {
        classes.push('text-nowrap');
    }

    return classes.join(' ');
}

// Estilos para th
function getThStyle(col) {
    if (col.column === 'check' || col.column === 'ID') {
        return {width: '1%'};
    }
    return {};
}

// Largura do skeleton baseada no tipo de coluna
function getSkeletonWidth(key) {
    const widths = {
        'check': '20px',
        'actions': '100px',
        'name': '70%',
        'email': '60%',
        'phone': '40%',
        'change_active': '60px',
    };
    return widths[key] || '50%';
}

// Classe para animação das linhas
function getRowClass(item, type) {
    if (type === 'row') {
        return 'table-row-animated';
    }
    return '';
}

// Obtém valor aninhado de um objeto
function getNestedValue(obj, path) {
    if (!path || !obj) return 'N/A';

    if (path.indexOf('.') === -1) {
        return obj[path] ?? 'N/A';
    }

    return path
        .split('.')
        .reduce((acc, key) => (acc && acc[key] !== undefined ? acc[key] : undefined), obj) ?? 'N/A';
}

// Muda o tipo de visualização
function changeViewType(view) {
    viewType.value = view;
    localStorage.setItem(`views-${props.session}`, view);

    if (view === 'table') {
        loadList();
    } else {
        emits('load-kanban');
    }
}

// Configura ordenação inicial
function setOrders() {
    const objData = JSON.parse(localStorage.getItem(props.session));

    if (!objData) {
        setTimeout(() => setOrders(), 300);
    } else {
        order.value = objData.params.order;
        order_by.value = objData.params.order_by;
    }
}

// Handler de ordenação
function handleSort(column) {
    const newOrder = (order_by.value === column && order.value === 'asc') ? 'desc' : 'asc';

    const obj = JSON.parse(localStorage.getItem(props.session));
    obj.params.order = newOrder;
    obj.params.order_by = column;
    localStorage.setItem(props.session, JSON.stringify(obj));

    order.value = newOrder;
    order_by.value = column;

    loadList();
}

// Scroll suave para o topo da tabela com offset
function scrollToTable() {
    const table = document.getElementById('crud-table');
    if (table) {
        const offset = 100;
        const top = table.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({top, behavior: 'smooth'});
    }
}

// Handler de mudança de página
function handlePageChange(newPage) {
    const obj = JSON.parse(localStorage.getItem(props.session));
    obj.params.page = newPage;
    localStorage.setItem(props.session, JSON.stringify(obj));

    loadList();
    scrollToTable();
}

// Busca dos dados da tabela
async function loadList() {
    if (!props.session) {
        setTimeout(() => loadList(), 300);
        return;
    }

    loading.value = true;

    try {
        setSessions(props.session);
        const data = await props.service.index(props.session);

        total.value = data.count;
        pages.value = data.pages;
        page.value = data.page;
        limit.value = parseInt(data.per_page);
        start.value = total.value === 0 ? 0 : ((page.value - 1) * limit.value) + 1;
        partial.value = total.value === 0 ? 0 : ((page.value - 1) * limit.value) + data.data.length;
        listAll.value = data.data;
    } finally {
        loading.value = false;
        endLoadTable();
    }
}

// Altera limite de registros por página
function setLimit(newLimit) {
    const obj = JSON.parse(localStorage.getItem(props.session));
    obj.params.page = 1;
    obj.params.limit = newLimit;
    localStorage.setItem(props.session, JSON.stringify(obj));

    loadList();
    scrollToTable();
}

// Seleciona/deseleciona todos os checkboxes
function selectAll() {
    const check = document.getElementById('selectAll')?.checked;

    if (check) {
        selectedIds.value = listAll.value.map(item => item.id);
    } else {
        selectedIds.value = [];
    }

    emits('update-show-card', check);
}

// Toggle selecao de um item individual
function toggleSelection(id) {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) {
        selectedIds.value.splice(index, 1);
    } else {
        selectedIds.value.push(id);
    }
}

// Limpa a selecao
function clearSelection() {
    selectedIds.value = [];
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
    }
}

// Handlers para bulk actions
async function handleBulkDelete() {
    emits('bulk-delete', [...selectedIds.value]);
}

async function handleBulkChangeActive(active) {
    emits('bulk-change-active', [...selectedIds.value], active);
}

async function handleBulkChangeStatus(status) {
    emits('bulk-change-status', [...selectedIds.value], status);
}

// Expoe metodos e dados para o componente pai
defineExpose({
    loadList,
    clearSelection,
    selectedIds
});

// Limpa selecao quando a lista muda
watch(listAll, () => {
    clearSelection();
});

// Lifecycle
onMounted(async () => {
    canCreate.value = await usePermissions.hasPermission(`${props.endpoint}.store`);

    if (viewType.value === 'card') {
        emits('load-kanban');
    } else {
        loadList();
    }
    setOrders();
});
</script>

<style scoped>
:deep(.table) {
    margin-bottom: 0;
}

:deep(.table th) {
    vertical-align: middle;
}

/* ===================== */
/* STICKY HEADER         */
/* ===================== */
.table-sticky-container {
    max-height: calc(100vh - 250px);
    overflow: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(var(--vz-primary-rgb), 0.35) transparent;
}

.table-sticky-container::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

.table-sticky-container::-webkit-scrollbar-track {
    background-color: transparent;
}

.table-sticky-container::-webkit-scrollbar-thumb {
    background-color: rgba(var(--vz-primary-rgb), 0.35);
    border-radius: 3px;
}

.table-sticky-container::-webkit-scrollbar-thumb:hover {
    background-color: rgba(var(--vz-primary-rgb), 0.55);
}

:deep(.table thead th) {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: var(--vz-card-bg-custom, #fff);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

/* ===================== */
/* PAGINAÇÃO RESPONSIVA  */
/* ===================== */
.pagination-wrapper {
    overflow-x: auto;
    max-width: 100%;
}

.pagination-wrapper :deep(.pagination) {
    flex-wrap: wrap;
}

@media (max-width: 575.98px) {
    .pagination-wrapper :deep(.page-link) {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
}

[data-layout-mode="dark"] :deep(.table thead th) {
    background-color: var(--vz-card-bg-custom, #212529);
}

:deep(.table td) {
    vertical-align: middle;
}

.icon-list {
    border: solid 1px #C6C6C6;
    border-radius: 5px;
}

/* ===================== */
/* SKELETON LOADING      */
/* ===================== */
.skeleton-container {
    min-height: 300px;
}

.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-shimmer 1.5s infinite;
    border-radius: 4px;
}

.skeleton-text {
    height: 16px;
    min-width: 40px;
}

.skeleton-row {
    animation: skeleton-fade-in 0.5s ease forwards;
    opacity: 0;
}

.skeleton-th {
    padding: 12px 16px;
}

@keyframes skeleton-shimmer {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

@keyframes skeleton-fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===================== */
/* EMPTY STATE           */
/* ===================== */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    animation: empty-state-bounce 2s ease-in-out infinite;
}

.empty-state-icon i {
    font-size: 2.5rem;
    color: #6c757d;
}

.empty-state-title {
    color: #495057;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.empty-state-text {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 300px;
    line-height: 1.6;
}

@keyframes empty-state-bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

/* ===================== */
/* ANIMAÇÃO DAS LINHAS   */
/* ===================== */
:deep(.table-row-animated) {
    animation: row-fade-in 0.4s ease forwards;
}

:deep(.table tbody tr:nth-child(1)) {
    animation-delay: 0.05s;
}

:deep(.table tbody tr:nth-child(2)) {
    animation-delay: 0.1s;
}

:deep(.table tbody tr:nth-child(3)) {
    animation-delay: 0.15s;
}

:deep(.table tbody tr:nth-child(4)) {
    animation-delay: 0.2s;
}

:deep(.table tbody tr:nth-child(5)) {
    animation-delay: 0.25s;
}

:deep(.table tbody tr:nth-child(6)) {
    animation-delay: 0.3s;
}

:deep(.table tbody tr:nth-child(7)) {
    animation-delay: 0.35s;
}

:deep(.table tbody tr:nth-child(8)) {
    animation-delay: 0.4s;
}

:deep(.table tbody tr:nth-child(9)) {
    animation-delay: 0.45s;
}

:deep(.table tbody tr:nth-child(10)) {
    animation-delay: 0.5s;
}

@keyframes row-fade-in {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* ===================== */
/* HOVER MELHORADO       */
/* ===================== */
:deep(.table-hover tbody tr) {
    transition: background-color 0.2s ease;
}

:deep(.table-hover tbody tr:hover) {
    background-color: rgba(64, 81, 137, 0.08) !important;
}

/* ===================== */
/* LOADING OVERLAY       */
/* ===================== */
.table-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(3px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: 0.25rem;
}

.loading-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.loading-text {
    margin: 0;
    color: #495057;
    font-size: 0.875rem;
    font-weight: 500;
}

/* ===================== */
/* TABELA LOADING        */
/* ===================== */
:deep(.table) {
    transition: opacity 0.5s ease 0.3s;
}

.table-loading {
    opacity: 0.4;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

/* ===================== */
/* TRANSIÇÕES            */
/* ===================== */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.table-rows-enter-active {
    transition: all 0.4s ease;
}

.table-rows-leave-active {
    transition: all 0.2s ease;
}

.table-rows-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.table-rows-leave-to {
    opacity: 0;
}

/* ===================== */
/* DARK MODE             */
/* ===================== */
[data-layout-mode="dark"] .skeleton {
    background: linear-gradient(90deg, #2a2f34 25%, #343a40 50%, #2a2f34 75%);
    background-size: 200% 100%;
}

[data-layout-mode="dark"] .table-loading-overlay {
    background: rgba(33, 37, 41, 0.9);
}

[data-layout-mode="dark"] .loading-content {
    background: #212529;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

[data-layout-mode="dark"] .loading-text {
    color: #ced4da;
}

[data-layout-mode="dark"] .empty-state-icon {
    background: linear-gradient(135deg, #2a2f34 0%, #343a40 100%);
}

[data-layout-mode="dark"] .empty-state-icon i {
    color: #adb5bd;
}

[data-layout-mode="dark"] .empty-state-title {
    color: #e9ecef;
}

[data-layout-mode="dark"] .empty-state-text {
    color: #adb5bd;
}

[data-layout-mode="dark"] :deep(.table-hover tbody tr:hover) {
    background-color: rgba(255, 255, 255, 0.05) !important;
}
</style>
