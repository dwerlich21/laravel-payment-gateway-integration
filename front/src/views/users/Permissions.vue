<!--eslint-disable no-mixed-spaces-and-tabs-->
<template>
  <div class="permissions-management">
    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
          Gerenciamento de Permissões
        </h5>
      </div>

      <div class="permissions-container">
        <BRow>
          <BCol
            md="4"
            class="mb-3"
          >
            <div class="input-group mb-3">
              <input
                v-model="permissionFilter"
                type="text"
                class="form-control"
                placeholder="Buscar permissão..."
              >
              <div class="input-group-append">
                <button
                  class="btn btn-light"
                  type="button"
                  @click="permissionFilter = ''"
                >
                  <i class="ri-close-line text-primary" />
                </button>
              </div>
            </div>
          </BCol>
        </BRow>

        <div class="form-check form-switch mb-3">
          <input
            id="selectAllPermissions"
            class="form-check-input"
            type="checkbox"
            role="switch"
            :checked="allPermissionsSelected"
            @click="handleSelectAll"
          >
          <label
            class="form-check-label"
            for="selectAllPermissions"
          >Selecionar Todas
          </label>
        </div>
      </div>

      <BRow>
        <BCol
          v-for="parent in filteredPermissions"
          :key="`parent-${parent.id}`"
          md="4"
          class="permission-group mb-5"
        >
          <!-- Grupos de permissões -->
          <div class="permission-parent p-2 border">
            <div class="form-check form-switch d-flex align-items-center">
              <input
                :id="`parent-${parent.id}`"
                class="form-check-input me-2"
                type="checkbox"
                role="switch"
                :checked="isParentSelected(parent.id)"
                @click="handleParentClick(parent.id)"
              >
              <label
                class="form-check-label w-100 d-flex justify-content-between align-items-center"
                :for="`parent-${parent.id}`"
              >
                <span
                  class="cursor-pointer"
                  @click.prevent="toggleGroupVisibility(parent.id)"
                >
                  {{ parent.description }}
                </span>
                <i
                  v-if="!expandedGroups[parent.id]"
                  class="ri-arrow-down-s-line cursor-pointer"
                  @click.prevent="toggleGroupVisibility(parent.id)"
                />
                <i
                  v-else
                  class="ri-arrow-up-s-line cursor-pointer"
                  @click.prevent="toggleGroupVisibility(parent.id)"
                />
              </label>
            </div>
          </div>

          <div
            v-show="expandedGroups[parent.id]"
            class="permission-children ps-4 border-top-0 border pt-2"
          >
            <div
              v-for="child in parent.children"
              v-show="matchesFilter(child)"
              :key="`child-${child.id}`"
              class="form-check form-switch mb-2"
            >
              <input
                :id="`child-${child.id}`"
                class="form-check-input"
                type="checkbox"
                role="switch"
                :checked="selectedIds.includes(child.id)"
                @click="handleChildClick(child.id, parent.id)"
              >
              <label
                class="form-check-label"
                :for="`child-${child.id}`"
              >
                {{ child.description }}
              </label>
            </div>
          </div>
        </BCol>
      </BRow>
    </div>
  </div>
</template>

<script setup>
import {ref, computed, watch, onMounted, defineProps, defineEmits} from 'vue';

// Props para o componente
const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    permissions: {
        type: Array,
        default: () => []
    }
});

// Emits para v-model
const emit = defineEmits(['update:modelValue']);

// Dados
const permissionFilter = ref(''); // Filtro para buscar permissões
const selectedIds = ref([]); // Array de IDs selecionados
const expandedGroups = ref({}); // Controla quais grupos estão abertos: {id: boolean}

// -------------------- COMPUTED PROPERTIES --------------------

// Filtra as permissões com base no termo de busca
const filteredPermissions = computed(() => {
    if (!permissionFilter.value) {
        return props.permissions;
    }

    const lowerFilter = permissionFilter.value.toLowerCase();

    return props.permissions.filter(parent => {
        // Verifica se o pai corresponde ao filtro
        const parentMatches = parent.description.toLowerCase().includes(lowerFilter);

        // Verifica se algum filho corresponde ao filtro
        const hasMatchingChild = parent.children.some(child =>
            child.description.toLowerCase().includes(lowerFilter)
        );

        return parentMatches || hasMatchingChild;
    });
});

// Verifica se todas as permissões estão selecionadas
const allPermissionsSelected = computed(() => {
    // Se não houver permissões, retorna falso
    if (props.permissions.length === 0) return false;

    // Verifica se todos os IDs estão selecionados
    let allIds = [];

    props.permissions.forEach(parent => {
        // Adiciona todos os IDs de filhos
        parent.children.forEach(child => {
            allIds.push(child.id);
        });
    });

    return allIds.every(id => selectedIds.value.includes(id));
});

// -------------------- MÉTODOS --------------------

// Verifica se um item corresponde ao filtro
function matchesFilter(item) {
    if (!permissionFilter.value) return true;

    const lowerFilter = permissionFilter.value.toLowerCase();
    return item.description.toLowerCase().includes(lowerFilter);
}

// Verifica se um pai está selecionado (todos os filhos selecionados)
function isParentSelected(parentId) {
    const parent = props.permissions.find(p => p.id === parentId);
    if (!parent) return false;

    // Um pai está selecionado se todos os seus filhos estiverem selecionados
    return parent.children.every(child => selectedIds.value.includes(child.id));
}

// Alternar a exibição de um grupo
function toggleGroupVisibility(parentId) {
    expandedGroups.value = {
        ...expandedGroups.value,
        [parentId]: !expandedGroups.value[parentId]
    };
}

// Trata o clique em um pai (seleção/deseleção)
function handleParentClick(parentId) {
    const parent = props.permissions.find(p => p.id === parentId);
    if (!parent) return;

    const shouldSelect = !isParentSelected(parentId);

    // Se devemos selecionar, adicionamos todos os filhos que não estão selecionados
    if (shouldSelect) {
        // Cria uma nova array com os IDs atuais
        const newSelectedIds = [...selectedIds.value];

        // Adiciona os IDs dos filhos (se ainda não estiverem incluídos)
        parent.children.forEach(child => {
            if (!newSelectedIds.includes(child.id)) {
                newSelectedIds.push(child.id);
            }
        });

        // Adiciona o ID do pai se não estiver incluído
        if (!newSelectedIds.includes(parentId)) {
            newSelectedIds.push(parentId);
        }

        selectedIds.value = newSelectedIds;
    } else {
        // Se devemos desselecionar, removemos todos os filhos
        selectedIds.value = selectedIds.value.filter(id => {
            // Mantém os IDs que não são do pai nem dos filhos
            return id !== parentId && !parent.children.some(child => child.id === id);
        });
    }

    // Expande o grupo se estiver selecionando
    if (shouldSelect && !expandedGroups.value[parentId]) {
        expandedGroups.value[parentId] = true;
    }

    // Emite a atualização
    emitUpdate();
}

// Trata o clique em um filho (seleção/deseleção)
function handleChildClick(childId, parentId) {
    // Verifica se o filho já está selecionado
    const isSelected = selectedIds.value.includes(childId);

    if (isSelected) {
        // Remove o filho e possivelmente o pai da seleção
        selectedIds.value = selectedIds.value.filter(id => id !== childId && id !== parentId);
    } else {
        // Adiciona o filho à seleção
        selectedIds.value = [...selectedIds.value, childId];
    }

    // Verifica se todos os filhos estão selecionados para atualizar o estado do pai
    const parent = props.permissions.find(p => p.id === parentId);
    if (parent) {
        const allChildrenSelected = parent.children.every(child =>
            selectedIds.value.includes(child.id)
        );

        if (allChildrenSelected) {
            // Se todos os filhos estão selecionados, adiciona o pai (se ainda não estiver)
            if (!selectedIds.value.includes(parentId)) {
                selectedIds.value = [...selectedIds.value, parentId];
            }
        } else {
            // Se não, remove o pai (se estiver)
            if (selectedIds.value.includes(parentId)) {
                selectedIds.value = selectedIds.value.filter(id => id !== parentId);
            }
        }
    }

    // Emite a atualização
    emitUpdate();
}

// Seleciona ou desseleciona todas as permissões
function handleSelectAll() {
    if (allPermissionsSelected.value) {
        // Desseleciona tudo
        selectedIds.value = [];
    } else {
        // Seleciona tudo
        const newSelectedIds = [];

        props.permissions.forEach(parent => {
            newSelectedIds.push(parent.id);

            parent.children.forEach(child => {
                newSelectedIds.push(child.id);
            });

            // Expande todos os grupos
            expandedGroups.value[parent.id] = true;
        });

        selectedIds.value = newSelectedIds;
    }

    // Emite a atualização
    emitUpdate();
}

// Emite a atualização para o v-model
function emitUpdate() {
    emit('update:modelValue', selectedIds.value);
}

// Inicializa as permissões selecionadas com base no modelValue
function initializePermissions() {
    // Converte os IDs para números para garantir consistência
    selectedIds.value = props.modelValue.map(id => Number(id));

    // Inicializa os grupos expandidos
    props.permissions.forEach(parent => {
        // Expande o grupo se o pai ou algum filho estiver selecionado
        const isParentOrChildSelected =
            selectedIds.value.includes(parent.id) ||
            parent.children.some(child => selectedIds.value.includes(child.id));

        expandedGroups.value[parent.id] = isParentOrChildSelected;
    });
}

// -------------------- WATCHERS --------------------

// Observa mudanças nas permissões
watch(() => props.permissions, () => {
    initializePermissions();
}, {deep: true});

// Observa mudanças no modelValue
watch(() => props.modelValue, () => {
    initializePermissions();
}, {deep: true});

// -------------------- LIFECYCLE HOOKS --------------------

// Inicialização ao montar
onMounted(() => {
    initializePermissions();
});
</script>

<style scoped>
.permission-parent {
    cursor: pointer;
    border-top-right-radius: 4px;
    border-top-left-radius: 4px;
}

.permission-children {
    animation: slideDown 0.3s ease-in-out;
    border-bottom-right-radius: 4px;
    border-bottom-left-radius: 4px;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>