<template>
    <div class="mb-4 card-border">
        <BCardHeader>
            <h5 class="mb-0">
                Permissões
            </h5>
        </BCardHeader>
        <BCardBody>
            <div v-if="loading" class="text-center py-4">
                <BSpinner />
                <p class="mt-2 text-muted">Carregando permissões...</p>
            </div>

            <div v-else-if="permissionsList.length === 0" class="text-center py-4">
                <p class="text-muted">Nenhuma permissão disponível</p>
            </div>

            <BRow v-else>
                <BCol
                    v-for="module in permissionsList"
                    :key="module.id"
                    md="6"
                    lg="4"
                    class="mb-4"
                >
                    <div class="permission-module">
                        <!-- Módulo pai com checkbox -->
                        <div class="form-check mb-2">
                            <input
                                :id="`module-${module.id}`"
                                type="checkbox"
                                class="form-check-input"
                                :checked="isModuleChecked(module)"
                                :indeterminate.prop="isModuleIndeterminate(module)"
                                @change="toggleModule(module, $event.target.checked)"
                            >
                            <label
                                :for="`module-${module.id}`"
                                class="form-check-label fw-semibold"
                            >
                                {{ module.description }}
                            </label>
                        </div>

                        <!-- Permissões filhas -->
                        <div class="ms-4">
                            <div
                                v-for="child in module.children"
                                :key="child.id"
                                class="form-check"
                            >
                                <input
                                    :id="`permission-${child.id}`"
                                    type="checkbox"
                                    class="form-check-input"
                                    :checked="isChecked(child.id)"
                                    :value="child.id"
                                    @change="togglePermission(child.id, $event.target.checked)"
                                >
                                <label
                                    :for="`permission-${child.id}`"
                                    class="form-check-label"
                                >
                                    {{ child.description }}
                                </label>
                            </div>
                        </div>
                    </div>
                </BCol>
            </BRow>

            <!-- Ações rápidas -->
            <div class="border-top pt-3 mt-3">
                <BButton
                    variant="outline-primary"
                    size="sm"
                    class="me-2"
                    @click="selectAll"
                >
                    Selecionar Todas
                </BButton>
                <BButton
                    variant="outline-secondary"
                    size="sm"
                    @click="deselectAll"
                >
                    Limpar Seleção
                </BButton>
            </div>
        </BCardBody>
    </div>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import http from '@/http';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);

const loading = ref(true);
const permissionsList = ref([]);

// Verifica se uma permissão está selecionada
function isChecked(id) {
    return props.modelValue.includes(id);
}

// Alterna uma permissão individual
function togglePermission(id, checked) {
    let newValue = [...props.modelValue];
    if (checked) {
        if (!newValue.includes(id)) {
            newValue.push(id);
        }
    } else {
        newValue = newValue.filter(i => i !== id);
    }
    emit('update:modelValue', newValue);
}

// Carrega permissões da API
async function loadPermissions() {
    try {
        loading.value = true;
        const response = await http.get('permissions');
        permissionsList.value = response.data.data;
    } catch (error) {
        console.error('Erro ao carregar permissões:', error);
    } finally {
        loading.value = false;
    }
}

// Verifica se todas as permissões do módulo estão selecionadas
function isModuleChecked(module) {
    if (!module.children || module.children.length === 0) {
        return props.modelValue.includes(module.id);
    }
    return module.children.every(child =>
        props.modelValue.includes(child.id)
    );
}

// Verifica se algumas (mas não todas) permissões do módulo estão selecionadas
function isModuleIndeterminate(module) {
    if (!module.children || module.children.length === 0) {
        return false;
    }
    const checkedCount = module.children.filter(child =>
        props.modelValue.includes(child.id)
    ).length;
    return checkedCount > 0 && checkedCount < module.children.length;
}

// Alterna todas as permissões de um módulo
function toggleModule(module, checked) {
    let newValue = [...props.modelValue];

    if (!module.children || module.children.length === 0) {
        if (checked) {
            if (!newValue.includes(module.id)) {
                newValue.push(module.id);
            }
        } else {
            newValue = newValue.filter(id => id !== module.id);
        }
        emit('update:modelValue', newValue);
        return;
    }

    const childIds = module.children.map(child => child.id);

    if (checked) {
        // Adiciona todas as permissões filhas
        childIds.forEach(id => {
            if (!newValue.includes(id)) {
                newValue.push(id);
            }
        });
    } else {
        // Remove todas as permissões filhas
        newValue = newValue.filter(id => !childIds.includes(id));
    }

    emit('update:modelValue', newValue);
}

// Seleciona todas as permissões
function selectAll() {
    const allIds = [];
    permissionsList.value.forEach(module => {
        allIds.push(module.id);
        if (module.children) {
            module.children.forEach(child => allIds.push(child.id));
        }
    });
    emit('update:modelValue', allIds);
}

// Remove todas as seleções
function deselectAll() {
    emit('update:modelValue', []);
}

onMounted(() => {
    loadPermissions();
});
</script>

<style scoped>
.permission-module {
    background-color: var(--vz-light);
    border-radius: 0.375rem;
    padding: 1rem;
}

.form-check-label {
    cursor: pointer;
    user-select: none;
}

.form-check-input:indeterminate {
    background-color: var(--vz-primary);
    border-color: var(--vz-primary);
}
</style>
