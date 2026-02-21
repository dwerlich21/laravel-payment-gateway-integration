<template>
    <PageForm
        title="Meu Perfil"
        title-header="Meu Perfil"
        @submit-form="submitForm"
    >
        <template #form>
            <div>
                <form id="form">
                    <!-- Seção 1: Informações Básicas -->
                    <BasicInfoSection
                        v-model:form-data="formData"
                        :access-level-options="accessLevels"
                        :data="formData"
                        :errors="errors"
                        :disabled="true"
                        :show-password-field="showPasswordField"
                        :show-password="showPassword"
                        @toggle-password="showPassword = !showPassword"
                        @handle-image="handleImage"
                        @set-image="setImage"
                        @reset-image="resetImageBlob('user-img-file-input', formData, 'img')"
                    />
                </form>
            </div>
        </template>
    </PageForm>
</template>

<script setup>
import {onMounted, ref} from 'vue';
import {useRoute} from 'vue-router';
import PageForm from "@/components/base/PageForm.vue";
import UserService from '@/services/UserService';
import {encodeId, Forbidden} from "@/composables/functions";
import {handleImg, resetImageBlob, setImageBlob} from "@/composables/img";
import {useAuthStore} from "@/stores/auth.js";

// Componentes das seções
import BasicInfoSection from "@/views/users/form/BasicInfoSection.vue";
import env from "@/env.js";
import {notifySuccess} from "@/composables/messages.js";

// Criando instância do serviço
const userService = new UserService();
const route = useRoute();
const authStore = useAuthStore();

// Estado do formulário
const formData = ref(userService.getDefaultFormData());
const showPasswordField = ref(false);
const showPassword = ref(false);
const errors = ref({});

// Níveis de acesso
const accessLevels = [
    {value: 'master', label: 'Master'},
    {value: 'admin', label: 'Administrador'},
    {value: 'secretary', label: 'Secretaria'},
];

// Constantes da página
const session = "Users";

async function loadUserData() {
    try {
        const user = authStore.getUser;

        if (!user || !user.id) {
            setTimeout(() => {
                loadUserData();
            }, 300)
        } else {

            const id = encodeId(user.id);
            const data = await userService.getById(id);

            formData.value = {
                ...formData.value,
                ...data,
                id: data.id
            }

            if (data.avatar) formData.value.avatar = `${env.url}${data.avatar}`

        }
    } catch (error) {
        console.error('Erro ao carregar usuário:', error);
        Forbidden(error);
    }
}

async function handleImage(event) {
    try {
        formData.value = await handleImg(event, 600, 600, formData.value, 'img');
    } catch (error) {
        console.error("Erro ao carregar a imagem:", error);
    }
}

function setImage(blob) {
    formData.value = setImageBlob(blob, formData.value, 'img');
}

async function submitForm() {
    try {
        await userService.save(formData.value);

        notifySuccess('Perfil atualizado com sucesso!');

        authStore.refresh();
    } catch (error) {
        console.error('error', error)
        if (error.data.errors) errors.value = error.data.errors;
    }
}

async function setAddress() {
    try {
        const address = await userService.getAddressByZipCode(formData.value.address.zipCode);

        formData.value.address.uf = address.uf;
        formData.value.address.city = address.city;
        formData.value.address.neighborhood = address.neighborhood;
        formData.value.address.address = address.address;
        formData.value.address.complement = address.complement;
    } catch (error) {
        console.error('Erro ao buscar endereço:', error);
    }
}

onMounted(async () => {
    await loadUserData();
});
</script>

<style scoped>
.profile-user {
    position: relative;
}

.profile-img-file-input {
    position: absolute;
    width: 0;
    height: 0;
    overflow: hidden;
    opacity: 0;
}

.profile-photo-edit {
    position: absolute;
    right: 0;
    bottom: 0;
    cursor: pointer;
}

.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.user-profile-image {
    width: 150px;
    height: 150px;
    object-fit: cover;
}
</style>