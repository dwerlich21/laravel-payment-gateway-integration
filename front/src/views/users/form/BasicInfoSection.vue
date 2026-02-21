<template>
    <div class="mb-4 card-border">
        <BCardHeader>
            <h5 class="mb-0">
                Informações Básicas
            </h5>
        </BCardHeader>
        <BCardBody>
            <BRow>
                <BCol
                    md="3"
                    class="text-center mb-3 d-flex align-items-center"
                >
                    <!-- Componente Cropper para imagem -->
                    <div class="text-center d-flex flex-column justify-content-center mb-3 w-100">
                        <Cropper
                            v-if="formData.img && !formData.imgUrl"
                            :proportion="1"
                            :img="formData.img"
                            @set-img="$emit('set-image', $event)"
                            @reset-logo="$emit('reset-image')"
                        />
                        <div
                            v-else
                            class="profile-user position-relative d-inline-block mx-auto mb-2"
                        >
                            <img
                                v-if="formData.imgUrl"
                                :src="formData.imgUrl"
                                class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                alt="imagem-usuário"
                            >

                            <img
                                v-else-if="formData.avatar"
                                :src="formData.avatar"
                                class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                alt="imagem-usuário"
                            >

                            <img
                                v-else
                                src="@/assets/images/user.jpg"
                                class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                alt="imagem-usuário-padrao"
                            >

                            <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                <input
                                    id="user-img-file-input"
                                    type="file"
                                    class="profile-img-file-input"
                                    accept="image/*"
                                    @change="$emit('handle-image', $event)"
                                >
                                <label
                                    for="user-img-file-input"
                                    class="profile-photo-edit avatar-xs"
                                >
                                    <span class="avatar-title rounded-circle bg-light text-body">
                                        <i class="ri-camera-fill"/>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </BCol>

                <BCol md="9">
                    <BRow>
                        <BCol
                            md="4"
                            class="mb-3"
                        >
                            <label for="name">Nome
                                <span class="text-danger">*</span>
                            </label>

                            <BFormInput
                                id="name"
                                name="name"
                                type="text"
                                placeholder="Nome"
                                required
                                :value="formData.name"
                                :state="getFieldState('basicInfo.name')"
                                aria-describedby="basicInfo-name-error-feedback"
                                @input="updateField('name', $event)"
                            />
                            <BFormInvalidFeedback
                                v-if="getFieldError('basicInfo.name')"
                                id="basicInfo-name-error-feedback"
                            >
                                {{ getFieldError('basicInfo.name') }}
                            </BFormInvalidFeedback>
                        </BCol>

                        <BCol
                            md="4"
                            class="mb-3"
                        >
                            <label for="email">E-mail
                                <span class="text-danger">*</span>
                            </label>
                            <BFormInput
                                id="email"
                                name="email"
                                type="email"
                                placeholder="E-mail"
                                required
                                :value="formData.email"
                                :state="getFieldState('basicInfo.email')"
                                aria-describedby="basicInfo-email-error-feedback"
                                @input="updateField('email', $event)"
                            />
                            <BFormInvalidFeedback
                                v-if="getFieldError('basicInfo.email')"
                                id="basicInfo-email-error-feedback"
                            >
                                {{ getFieldError('basicInfo.email') }}
                            </BFormInvalidFeedback>
                        </BCol>

                        <BCol
                            md="4"
                            class="mb-3"
                        >
                            <label for="cpf">CPF
                                <span class="text-danger">*</span>
                            </label>
                            <BFormInput
                                id="cpf"
                                v-maska="'###.###.###-##'"
                                name="cpf"
                                type="text"
                                placeholder="___.___.___-__"
                                required
                                :value="formData.cpf"
                                :state="getFieldState('basicInfo.cpf')"
                                aria-describedby="basicInfo-cpf-error-feedback"
                                @input="updateField('cpf', $event)"
                            />
                            <BFormInvalidFeedback
                                v-if="getFieldError('basicInfo.cpf')"
                                id="basicInfo-cpf-error-feedback"
                            >
                                {{ getFieldError('basicInfo.cpf') }}
                            </BFormInvalidFeedback>
                        </BCol>

                        <BCol
                            md="4"
                            class="mb-3"
                        >
                            <label for="position">Cargo
                                <span class="text-danger">*</span>
                            </label>
                            <BFormInput
                                id="position"
                                name="position"
                                type="text"
                                placeholder="Nome"
                                required
                                :value="formData.position"
                                :state="getFieldState('basicInfo.position')"
                                aria-describedby="basicInfo-position-error-feedback"
                                @input="updateField('position', $event)"
                            />
                            <BFormInvalidFeedback
                                v-if="getFieldError('basicInfo.position')"
                                id="basicInfo-position-error-feedback"
                            >
                                {{ getFieldError('basicInfo.position') }}
                            </BFormInvalidFeedback>

                        </BCol>

                        <BCol
                            md="4"
                            class="mb-3"
                        >
                            <BaseMultiselect
                                v-model="formData.type"
                                :options="accessLevelOptions"
                                :create-option="false"
                                :disabled="disabled"
                                required
                                label="Nível de Acesso"
                                placeholder="Selecione o nível de acesso"
                                :errors="getFieldErrors('basicInfo.type')"
                            />
                        </BCol>

                        <BCol
                            md="4"
                            class="mb-3"
                        >
                            <label for="phone">Telefone
                                <span class="text-danger">*</span>
                            </label>
                            <BFormInput
                                id="phone"
                                v-maska="['(##) ####-####', '(##) #####-####']"
                                name="phone"
                                type="text"
                                placeholder="(99) 99999-9999"
                                required
                                :value="formData.phone"
                                :state="getFieldState('basicInfo.phone')"
                                aria-describedby="basicInfo-phone-error-feedback"
                                @input="updateField('phone', $event)"
                            />
                            <BFormInvalidFeedback
                                v-if="getFieldError('basicInfo.phone')"
                                id="basicInfo-phone-error-feedback"
                            >
                                {{ getFieldError('basicInfo.phone') }}
                            </BFormInvalidFeedback>
                        </BCol>

                        <BCol
                            v-if="formData.id === 0 || showPasswordField"
                            md="4"
                            class="mb-3"
                        >
                            <label for="password">Senha
                                <span
                                    v-if="formData.id === 0"
                                    class="text-danger"
                                >*
                                </span>
                            </label>
                            <div class="input-group">
                                <BFormInput
                                    id="password"
                                    name="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="Senha"
                                    :required="formData.id === 0"
                                    :value="formData.password"
                                    :state="getFieldState('basicInfo.password')"
                                    aria-describedby="basicInfo-password-error-feedback"
                                    @input="updateField('password', $event)"
                                />
                                <BButton
                                    variant="light"
                                    type="button"
                                    @click="$emit('toggle-password')"
                                >
                                    <i :class="showPassword ? 'ri-eye-off-line' : 'ri-eye-line'"/>
                                </BButton>
                            </div>
                            <small class="text-muted">Mínimo 8 caracteres</small>
                            <BFormInvalidFeedback
                                v-if="getFieldError('basicInfo.password')"
                                id="basicInfo-password-error-feedback"
                            >
                                {{ getFieldError('basicInfo.password') }}
                            </BFormInvalidFeedback>
                        </BCol>
                    </BRow>
                </BCol>
            </BRow>
        </BCardBody>
    </div>
</template>

<script setup>
import {defineProps, defineEmits} from 'vue';

import BaseMultiselect from "@/components/base/BaseMultiselect.vue";
import Cropper from "@/components/base/Cropper.vue";

const props = defineProps({
    formData: {
        type: Object,
        default: () => ({})
    },
    positionOptions: {
        type: Array,
        default: () => []
    },
    accessLevelOptions: {
        type: Array,
        default: () => []
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    showPasswordField: {
        type: Boolean,
        default: false
    },
    showPassword: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:formData', 'handle-image', 'set-image', 'reset-image', 'toggle-password']);


// Helper functions for validation styling
function getFieldError(fieldName) {
    return props.errors[fieldName]?.[0] || null;
}

function getFieldErrors(fieldName) {
    return props.errors[fieldName] || [];
}

function getFieldState(fieldName) {
    if (props.errors[fieldName]) {
        return false;
    }
    return null;
}

// Funções para atualizar campos específicos
function updateField(fieldName, value) {
    const newData = {...props.formData};
    newData[fieldName] = value;
    emit('update:formData', newData);
}
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