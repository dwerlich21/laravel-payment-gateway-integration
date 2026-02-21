<template>
    <PageForm
        :title="formData.id ? `Editar Usuário: ${formData.basicInfo?.name || 'Usuário'}` : 'Novo Usuário'"
        :title-header="formData.id ? 'Edição de Usuário' : 'Cadastro de Usuário'"
        :session="session"
        @submit-form="submitForm"
    >
        <template #form>
            <div>
                <form id="form" class="mb-3">
                    <!-- Seção 1: Informações Básicas -->
                    <BasicInfoSection
                        v-model:form-data="formData.basicInfo"
                        :access-level-options="accessLevels"
                        :data="formData.basicInfo"
                        :errors="errors"
                        :show-password-field="showPasswordField"
                        :show-password="showPassword"
                        @toggle-password="showPassword = !showPassword"
                        @handle-image="handleImage"
                        @set-image="setImage"
                        @reset-image="resetImageBlob('user-img-file-input', formData.basicInfo, 'img')"
                    />
                    <!-- Seção 2: Endereço -->
                    <AddressSection
                        v-model:form-data="formData.address"
                        :data="formData.address"
                        :errors="errors"
                        @set-address="setAddress"
                    />

                    <!-- Seção 3: Permissões -->
                    <PermissionsSection
                        v-model="formData.permissions"
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
import {Forbidden} from "@/composables/functions";
import {handleImg, resetImageBlob, setImageBlob} from "@/composables/img";

// Componentes das seções
import BasicInfoSection from "@/views/users/form/BasicInfoSection.vue";
import AddressSection from "@/views/users/form/AddressSection.vue";
import PermissionsSection from "@/views/users/form/PermissionsSection.vue";
import router from "@/router";
import env from "@/env.js";

// Criando instância do serviço
const userService = new UserService();
const route = useRoute();

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

async function loadUserData(id) {
    try {
        const data = await userService.getById(id);

        formData.value = {
            ...formData.value,
            ...data,
            id: data.basicInfo.id
        }

        if(data.basicInfo.avatar) formData.value.basicInfo.avatar = `${env.url}${data.basicInfo.avatar}`

    } catch (error) {
        console.error('Erro ao carregar usuário:', error);
        Forbidden(error);
    }
}

async function handleImage(event) {
    try {
        formData.value.basicInfo = await handleImg(event, 600, 600, formData.value.basicInfo, 'img');
    } catch (error) {
        console.error("Erro ao carregar a imagem:", error);
    }
}

function setImage(blob) {
    formData.value.basicInfo = setImageBlob(blob, formData.value.basicInfo, 'img');
}

async function submitForm() {
    try {
        await userService.save(formData.value);

        router.back();
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
    try {
        const userId = route.params.id;

        if (userId) {
            await loadUserData(userId);
        }
    } catch (error) {
        console.error('Erro ao carregar dados iniciais:', error);
    }
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