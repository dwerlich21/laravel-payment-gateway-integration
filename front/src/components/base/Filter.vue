<script setup>
import {ref, onMounted, defineProps, defineEmits, computed, watch} from 'vue';

import {encodeId, getUrl} from '@/composables/functions';
import {setSessions} from '@/composables/setSessions';
import {maskCpfCnpj, maskDate, maskPhone} from "@/composables/masks";
import {usePermissions} from "@/utils/permissions.js";

import Multiselect from '@vueform/multiselect';
import '@vueform/multiselect/themes/default.css';

import env from '@/env'
import {useAuthStore} from '@/stores/auth.js';

const props = defineProps({
    session: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    titlePluralize: {
        type: String,
        required: true,
    },
    filter: {
        type: Object,
        required: true,
    },
    url: {
        type: String,
        required: true,
    },
    endpoint: {
        type: String,
        required: true,
    },
    views: {
        type: Boolean,
        default: false,
    },
    hideAddButton: {
        type: Boolean,
        default: false,
    },
    export_pdf: {
        type: Boolean,
        default: false,
    },
    export_csv: {
        type: Boolean,
        default: false,
    },
    button_modal: {
        type: Boolean,
        default: false,
    },
});

const formData = ref({});
const showFilter = ref(false);
const countFilter = ref(0);
const canCreate = ref(false);

const emit = defineEmits(['new-view', 'update-show-card', 'filter-applied']);
const viewsType = ref(localStorage.getItem(`views-${props.session}`) || 'table');

const authStore = useAuthStore();

const currentUser = computed(() => {
    return authStore.currentUser || {}
});

watch(() => formData.value, (newValue) => {
    const excluded = ['page', 'order_by', 'order', 'limit', 'global'];

    let count = 0;
    Object.keys(newValue).forEach(key => {
        if (excluded.indexOf(key) < 0 && newValue[key] !== '' && newValue[key] !== null) {
            ++count;
        }
    })

    countFilter.value = count;
}, {deep: true});

function setFilter() {
    const obj = JSON.parse(localStorage.getItem(props.session)) || {};

    const keys = Object.keys(obj.params);

    keys.forEach(key => {
        obj.params[key] = formData.value[key] || '';
    });

    obj.params.page = 1;
    localStorage.setItem(props.session, JSON.stringify(obj));
    emit('filter-applied');
}

const getFilter = () => {
    const obj = JSON.parse(localStorage.getItem(props.session));

    if (!obj) {
        setTimeout(() => {
            getFilter();
        }, 300);
    } else {
        formData.value = obj.params;
    }
};

const resetTable = () => {
    localStorage.removeItem(props.session);
    setSessions(props.session);
    getFilter();
    emit('filter-applied');
};

function newView(view) {
    viewsType.value = view;
    localStorage.setItem(`views-${props.session}`, view);
    emit('new-view', view);
}

function exportFile(type) {
    if (!currentUser.value || Object.keys(currentUser.value || {}).length === 0) {
        setTimeout(() => {
            exportFile(type);
        }, 300)
    } else {

        let newWindow = '';

        const cabinet = encodeId(currentUser.value.cabinet_id);

        let url = `/?cabinet=${cabinet}`;
        const keys = Object.keys(formData.value || {});

        for (let i = 0; i < keys.length; i++) {
            if (keys[i] !== 'page' && keys[i] !== 'limit') {
                let result = formData.value[keys[i]];
                url += `&${keys[i]}=${result}`;
            }
        }

        let obj = JSON.parse(localStorage.getItem(props.session));

        newWindow = window.open(
            `${env.url}reports/${type}/${obj.url}${url}`,
            '_blank'
        )

        if (newWindow) {
            newWindow.focus();
        }
    }
}

function getMask(input) {
    switch (input.mask) {
        case 'date':
            formData.value[input.name] = maskDate(formData.value[input.name]);
            break;
        case 'phone':
            formData.value[input.name] = maskPhone(formData.value[input.name]);
            break;
        case 'cpf':
            formData.value[input.name] = maskCpfCnpj(formData.value[input.name]);
            break;
    }
}

onMounted(async () => {
    getFilter();
    canCreate.value = await usePermissions.hasPermission(`${props.endpoint}.store`);
});
</script>

<template>
    <BCardHeader class="border border-dashed border-end-0 border-start-0 border-top-0">
        <div class="d-md-flex align-items-center">
            <h5 class="card-title mb-0 flex-grow-1">
                {{ titlePluralize }}
            </h5>
            <div
                v-if="title"
                class="flex-shrink-0"
            >
                <div class="d-lg-flex flex-wrap gap-2">
                    <div class="app-search d-none d-md-block p-0">
                        <div class="position-relative">
                            <input
                                v-model="formData.global"
                                type="text"
                                class="form-control"
                                placeholder="Busca global"
                                autocomplete="off"
                                @keyup.enter="setFilter()"
                            >
                            <span class="mdi mdi-magnify search-widget-icon"/>
                            <span
                                class="mdi mdi-close pointer close-widget-icon"
                                @click="formData.global = ''; setFilter()"
                            />
                        </div>
                    </div>

                    <BButton
                        v-if="views"
                        id="grid-view-button"
                        v-b-tooltip.hover="'Visualizar em cards'"
                        type="button"
                        class="btn btn-soft-info nav-link btn-icon fs-14 filter-button"
                        :class="{'active' : viewsType === 'card'}"
                        @click="newView('card')"
                    >
                        <i class="ri-grid-fill"/>
                    </BButton>

                    <BButton
                        v-if="views"
                        id="list-view-button"
                        v-b-tooltip.hover="'Visualizar em tabela'"
                        type="button"
                        class="btn btn-soft-info nav-link  btn-icon fs-14 filter-button ms-2"
                        :class="{'active' : viewsType === 'table'}"
                        @click="newView('table')"
                    >
                        <i class="ri-list-unordered"/>
                    </BButton>

                    <BButton
                        v-if="export_pdf"
                        id="list-view-button"
                        type="button"
                        title="Exportar CSV"
                        class="btn btn-soft-danger btn-icon fs-14 filter-button ms-2"
                        @click="exportFile('pdf')"
                    >
                        <i class="ri-file-pdf-fill"/>
                    </BButton>

                    <BButton
                        v-if="export_csv"
                        id="list-view-button"
                        type="button"
                        title="Exportar CSV"
                        class="btn btn-soft-success btn-icon fs-14 filter-button ms-2"
                        @click="exportFile('csv')"
                    >
                        <i class="ri-file-excel-2-fill"/>
                    </BButton>

                    <BButton
                        v-if="Object.keys(formData || {}).length > 0"
                        v-b-tooltip.hover="!showFilter ? 'Exibir Filtros' : 'Esconder Filtros'"
                        type="button"
                        class="btn bg-soft-secondary nav-link btn-icon fs-14 filter-button"
                        @click="showFilter = !showFilter"
                    >
                        <span
                            v-if="countFilter > 0"
                            class="badge bg-info position-absolute top-0 start-90 translate-middle px-1"
                        >{{ countFilter }}
                        </span>
                        <i class="ri-filter-2-fill"/>
                    </BButton>

                    <BButton
                        v-if="button_modal && canCreate"
                        variant="primary"
                        class="add-btn"
                        @click="openModal"
                    >
                        <i class="ri-add-line align-bottom me-1"/>
                        {{ title }}
                    </BButton>

                    <router-link
                        v-else-if="canCreate && !hideAddButton"
                        class="btn btn-primary"
                        :to="{ name: `${endpoint}-form`}"
                    >
                        <i class="ri-add-line align-bottom me-1"/>
                        {{ title }}
                    </router-link>
                </div>
            </div>
        </div>
    </BCardHeader>

    <!-- Adicionando transição para o card body -->
    <transition name="slide-fade">
        <BCardBody
            v-if="showFilter"
            class="border border-dashed border-end-0 border-start-0 border-top-0 py-2 filter-container"
        >
            <div
                class="bg-light rounded-2 p-2"
                style="margin: 0 -10px"
            >
                <b-form
                    id="filter"
                    @submit.prevent="setFilter()"
                >
                    <BRow>
                        <BCol
                            v-for="(input, index) in filter"
                            :key="'input-filter-' + index"
                            class="my-1"
                            :md="input.col"
                        >
                            <Multiselect
                                v-if="input.options"
                                :id="input.name + 'Filter'"
                                v-model="formData[input.name]"
                                :loading="Object.keys(input.options).length === 0"
                                :mode="input.mode || 'single'"
                                :close-on-select="!input.mode || input.mode === 'single'"
                                :searchable="true"
                                :create-option="false"
                                :options="input.options"
                                :placeholder="input.placeholder"
                            />

                            <input
                                v-else
                                v-model="formData[input.name]"
                                class="form-control"
                                :placeholder="input.placeholder"
                                :name="input.name"
                                :type="input.type || 'text'"
                                @keyup="getMask(input)"
                            >
                        </BCol>

                        <div
                            v-if="Object.keys(filter).length > 0"
                            class="col d-flex justify-content-end my-1"
                        >
                            <button
                                class="btn btn-soft-danger ms-2"
                                style="height: 40px"
                                type="reset"
                                @click="resetTable"
                            >
                                Limpar
                            </button>

                            <button
                                type="submit"
                                class="btn btn-soft-success ms-2"
                                style="height: 40px"
                            >
                                Buscar
                            </button>
                        </div>
                    </BRow>
                </b-form>
            </div>
        </BCardBody>
    </transition>
</template>

<style scoped>
/* Estilos para a transição */
.slide-fade-enter-active {
    transition: all 0.5s cubic-bezier(0.05, 0.82, 0.14, 0.99); /* Transição mais suave ao aparecer */
}

.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1); /* Transição suave ao desaparecer */
}

.slide-fade-enter-from {
    transform: translateY(-10px); /* Menor movimento ao aparecer */
    opacity: 0; /* Começa invisível */
}

.slide-fade-leave-to {
    transform: translateY(-20px); /* Desliza para cima ao desaparecer */
    opacity: 0; /* Termina invisível */
}

/* Garante que o container de filtro tenha overflow hidden para a animação */
.filter-container {
    overflow: visible;
}
</style>