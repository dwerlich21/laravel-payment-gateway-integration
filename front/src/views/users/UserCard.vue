<template>
  <BRow v-if="users.length > 0">
    <BCol
      v-for="(data, index) of users"
      :key="index"
      xxl="3"
      lg="4"
      md="6"
      class="col-equal-height mb-4"
    >
      <div class="card-equal-height border rounded-3 mb-3 overflow-hidden hover-shadow">
        <!-- Cabeçalho do Card com Avatar e Status -->
        <div class="card-header p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex align-items-center">
              <div class="avatar-container">
                <img
                  v-if="data.img"
                  :src="`${env.url}uploads/users/${data.img}`"
                  alt=""
                  class="member-img img-fluid d-block rounded-circle"
                >
                <div
                  v-else
                  class="avatar-circle d-flex align-items-center justify-content-center"
                  :class="userService.generateAvatarColor(data.name)"
                >
                  {{ userService.generateNickname(data.name) }}
                </div>
              </div>
              <div class="ms-3">
                <h5 class="fs-16 mb-1 text-truncate card-title">
                  {{ data.name }}
                </h5>
                <p class="text-subtitle mb-0 fs-14">
                  {{ data?.position?.name }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Corpo do Card com Layout em Duas Colunas -->
        <BCardBody class="p-3 card-body-content">
          <div class="row">
            <!-- Coluna com Informações de Contato (lado esquerdo) -->
            <div class="col-7">
              <div class="contact-info">
                <div
                  v-if="data.city && data.state"
                  class="d-flex align-items-center mb-2"
                >
                  <i class="ri-map-pin-line contact-icon me-2 fs-16" />
                  <span class="contact-text fs-14">{{ data.city }}/{{ data.state }}</span>
                </div>

                <div
                  v-if="data.email"
                  class="d-flex align-items-center mb-2"
                >
                  <i class="ri-mail-line contact-icon me-2 fs-16" />
                  <span class="contact-text fs-14 text-break">{{ data.email }}</span>
                </div>

                <div
                  v-if="data.phone"
                  class="d-flex align-items-center mb-2"
                >
                  <i class="ri-phone-line contact-icon me-2 fs-16" />
                  <span class="contact-text fs-14">{{ maskPhone(data.phone) }}</span>
                </div>
              </div>
            </div>

            <!-- Informações Adicionais (lado direito) -->
            <div class="col-5 border-start ps-2">
              <div class="stats-info">
                <div class="d-flex align-items-center mb-2">
                  <i class="ri-shield-user-line stats-icon me-2 fs-16" />
                  <div class="stats-label fs-12">
                    Nível de Acesso
                  </div>
                </div>
                <div class="d-flex align-items-center">
                  <BBadge
                    :variant="getAccessLevelBadge(data.type)"
                    class="ms-3"
                  >
                    {{ getAccessLevelName(data.type) }}
                  </BBadge>
                </div>
              </div>
            </div>
          </div>

          <!-- CPF -->
          <div
            v-if="data.cpf"
            class="mt-3 d-flex align-items-center"
          >
            <i class="ri-id-card-line contact-icon me-2 fs-16" />
            <span class="contact-text fs-14">CPF: {{ data.cpf }}</span>
          </div>

          <!-- Botão de Permissões -->
          <!--                    <div class="mt-3 text-center details-btn-container">-->
          <!--                        <button-->
          <!--                            class="btn btn-sm btn-outline-primary w-100"-->
          <!--                            @click="viewPermissions(data)"-->
          <!--                        >-->
          <!--                            <i class="ri-lock-password-line me-1"></i>-->
          <!--                            Ver Permissões de Acesso-->
          <!--                        </button>-->
          <!--                    </div>-->
        </BCardBody>

        <!-- Rodapé do Card com Ações -->
        <BCardFooter class="p-0 card-footer-actions">
          <div class="d-flex w-100">
            <router-link
              :to="`/usuários/cadastrar/${data.id}`"
              class="flex-1 w-50"
            >
              <button class="btn action-btn edit-btn w-100 h-100">
                <i class="ri-pencil-line align-bottom me-1" />
                Editar
              </button>
            </router-link>

            <button
              class="btn action-btn delete-btn w-50 h-100"
              @click="deleteUser(data.id)"
            >
              <i class="ri-delete-bin-line align-bottom me-1" />
              Excluir
            </button>
          </div>
        </BCardFooter>
      </div>
    </BCol>
  </BRow>

  <!-- Mensagem quando não há dados -->
  <BRow v-else-if="!loading && users.length === 0">
    <BCol>
      <BBadge
        variant="light"
        class="w-100 p-3 fs-14"
      >
        Nenhum usuário encontrado
      </BBadge>
    </BCol>
  </BRow>

  <!-- Modal de Permissões -->
  <BModal
    v-model="showPermissionsModal"
    title="Permissões de Acesso"
    size="lg"
    centered
    hide-footer
  >
    <div v-if="selectedUser">
      <h5 class="mb-3">
        Permissões do usuário: {{ selectedUser.name }}
      </h5>
      <div class="permissions-list p-3 bg-light rounded">
        <div v-if="selectedUser.permissions && selectedUser.permissions.length > 0">
          <Permissions
            v-model="selectedUser.permissions"
            :permissions="permissions"
            disabled
          />
        </div>
        <div
          v-else
          class="text-center p-3"
        >
          <i class="ri-lock-line fs-3 text-muted" />
          <p class="mt-2 mb-0">
            Este usuário não possui permissões específicas atribuídas.
          </p>
        </div>
      </div>
      <div class="text-end mt-3">
        <button
          class="btn btn-sm btn-primary"
          @click="closePermissionsModal"
        >
          Fechar
        </button>
      </div>
    </div>
  </BModal>
</template>

<script setup>
import {ref, computed, onMounted, watch, onBeforeUnmount, defineProps} from 'vue';
import {maskPhone} from "@/composables/masks";
import {showAlertConfirm, Forbidden, getUrl} from "@/composables/functions";
import UserService from '@/services/UserService';
import Permissions from "@/views/users/Permissions.vue";
import {setSessions} from "@/composables/setSessions";
import http from "@/http";
import env from '@/env';
import {notifySuccess} from "@/composables/messages";

// Props e Emits
const props = defineProps({
    endpoint: {
        type: String,
        required: true
    },
    sessionName: {
        type: String,
        required: true
    }
});

// Serviço e dados
const userService = new UserService();
const loading = ref(false);
const search = ref(true);
const positions = ref([]);
const permissions = ref([]);

// Estado do modal de permissões
const showPermissionsModal = ref(false);
const selectedUser = ref(null);

// Níveis de acesso
const accessLevels = [
    {id: 1, name: 'Master', variant: 'danger'},
    {id: 0, name: 'Administrador', variant: 'primary'},
    {id: 2, name: 'Secretaria', variant: 'success'}
];

/**
 * Obtém o nome do nível de acesso com base no ID
 */
function getAccessLevelName(typeId) {
    const level = accessLevels.find(l => l.id === typeId);
    return level ? level.name : 'Não definido';
}

/**
 * Obtém a variante de badge para o nível de acesso
 */
function getAccessLevelBadge(typeId) {
    const level = accessLevels.find(l => l.id === typeId);
    return level ? level.variant : 'secondary';
}

/**
 * Exclui um usuário
 */
async function deleteUser(id) {
    try {
        const result = await showAlertConfirm('Os dados do usuário serão removidos e não poderão mais ser recuperados. ');

        if (result) {
            http.delete(`${props.endpoint}${id}`)
                .then(() => {
                    notifySuccess('Usuário excluído com sucesso!');
                    apiStore.deleteCard(id);
                })
                .catch(error => {
                    console.error('Erro ao excluir usuário:', error);
                    Forbidden(error);
                });
        }
    } catch (error) {
        console.error('Erro ao excluir usuário:', error);
        Forbidden(error);
    }
}

/**
 * Exibe modal com as permissões do usuário
 */
// function viewPermissions(user) {
//     selectedUser.value = user;
//     showPermissionsModal.value = true;
// }

/**
 * Fecha o modal de permissões
 */
function closePermissionsModal() {
    showPermissionsModal.value = false;
    selectedUser.value = null;
}

/**
 * Busca os dados dos usuários
 */
function getData() {
    if (!localStorage.getItem(props.sessionName)) {
        setSessions(props.sessionName);
    }

    const url = getUrl(props.sessionName);
    apiStore.getCardsApi(url);

    setTimeout(() => {
        search.value = true;
    }, 900);
}

/**
 * Manipulador de scroll para paginação infinita
 */
const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const threshold = document.body.offsetHeight - 300;

    if (scrollPosition >= threshold && search.value === true && pagination.value.total > pagination.value.partial) {
        search.value = false;

        let obj = JSON.parse(localStorage.getItem(props.sessionName));
        obj.params.index = obj.params.index + 1;
        localStorage.setItem(props.sessionName, JSON.stringify(obj));

        getData();
    }
};

// Inicialização
onMounted(async () => {
    loading.value = true;

    try {
        // Carrega cargos e permissões
        positions.value = await userService.getPositions();
        permissions.value = await userService.getPermissions();

        // Inicializa a listagem
        let obj = JSON.parse(localStorage.getItem(props.sessionName));
        obj.params.index = 0;
        localStorage.setItem(props.sessionName, JSON.stringify(obj));

        getData();

        // Adiciona listener de scroll para paginação infinita
        window.addEventListener('scroll', handleScroll);
    } catch (error) {
        console.error('Erro ao inicializar componente:', error);
    } finally {
        loading.value = false;
    }
});

// Observa mudanças no estado da loja
watch(() => apiStore.listCards, () => {
    loading.value = false;
}, {deep: true});

// Limpeza antes de desmontar o componente
onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.card-equal-height {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.hover-shadow {
    transition: all 0.3s ease;
    border-color: #dee2e6;
}

.hover-shadow:hover {
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
    border-color: #adb5bd;
    transform: translateY(-2px);
}

html[data-layout-mode="dark"] .hover-shadow {
    border-color: #343a40;
    background-color: #212529;
}

html[data-layout-mode="dark"] .card-footer-actions {
    border-top: 1px solid #343a40;
}

html[data-layout-mode="dark"] .hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.4);
    border-color: #495057;
    transform: translateY(-2px);
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

html[data-layout-mode="light"] .card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

html[data-layout-mode="dark"] .card-header {
    background: linear-gradient(to right, #212529, #343a40);
    border-bottom: 1px solid #343a40;
}

.avatar-container {
    width: 50px;
    height: 50px;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
}

.avatar-circle {
    width: 48px;
    height: 48px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.5rem;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    text-transform: uppercase;
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
}

.text-subtitle {
    color: #6c757d;
}

html[data-layout-mode="dark"] .card-title {
    color: #fff;
}

html[data-layout-mode="dark"] .text-subtitle {
    color: #9ca3af;
}

.avatar-bg {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 2px solid #fff;
}

.member-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.contact-info i {
    flex-shrink: 0;
}

.contact-icon, .stats-icon {
    color: #6c757d;
    flex-shrink: 0;
    width: 20px;
    text-align: center;
}

.contact-text {
    color: #6c757d;
}

.stats-value {
    color: #495057;
    font-size: 0.9rem;
}

.stats-label {
    color: #6c757d;
    line-height: 1;
}

html[data-layout-mode="dark"] .contact-icon,
html[data-layout-mode="dark"] .stats-icon {
    color: #adb5bd;
}

html[data-layout-mode="dark"] .contact-text,
html[data-layout-mode="dark"] .stats-label {
    color: #adb5bd;
}

html[data-layout-mode="dark"] .stats-value {
    color: #e9ecef;
}

html[data-layout-mode="dark"] .card-body-content {
    background-color: #212529;
}

.card-footer-actions {
    border-top: 1px solid #e4e9f0;
}

.action-btn {
    border-radius: 0;
    padding: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.edit-btn {
    color: #495057;
    background-color: #f8f9fa;
}

.edit-btn:hover {
    background-color: #e9ecef;
    color: #212529;
}

.delete-btn {
    color: #dc3545;
    background-color: #f8f9fa;
}

.delete-btn:hover {
    background-color: #fee2e2;
    color: #dc2626;
}

/* Estilo para modo escuro nos botões de footer */
html[data-layout-mode="dark"] .edit-btn {
    color: #fff;
    background-color: #343a40;
}

html[data-layout-mode="dark"] .edit-btn:hover {
    background-color: #495057;
    color: #fff;
}

html[data-layout-mode="dark"] .delete-btn {
    color: #fff;
    background-color: #212529;
}

html[data-layout-mode="dark"] .delete-btn:hover {
    background-color: #343a40;
    color: #fff;
    border-left: 1px solid #495057;
}

/* Estilos para o botão de detalhes */
.details-btn-container {
    height: 32px; /* Altura fixa para o botão de detalhes */
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-outline-primary {
    border-color: #ced4da;
    color: #495057;
    background-color: transparent;
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    height: 32px; /* Altura fixa para todos os botões de detalhes */
}

.btn-outline-primary:hover {
    background-color: #f8f9fa;
    color: #0d6efd;
    border-color: #0d6efd;
}

html[data-layout-mode="dark"] .btn-outline-primary {
    border-color: #495057;
    color: #adb5bd;
}

html[data-layout-mode="dark"] .btn-outline-primary:hover {
    background-color: #343a40;
    color: #63a0ff;
    border-color: #63a0ff;
}

/* Estilo para a borda entre as colunas */
@media (min-width: 768px) {
    .border-start {
        border-left: 1px solid #e9ecef !important;
    }

    html[data-layout-mode="dark"] .border-start {
        border-left: 1px solid #343a40 !important;
    }
}
</style>