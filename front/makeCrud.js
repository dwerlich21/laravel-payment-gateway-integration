const fs = require('fs');
const path = require('path');
const pluralize = require('pluralize');

// Função principal
function makeCrud(model) {
    const capitalizedModel = capitalize(model);
    makeConstants(model);
    makeView(model);
    makeSession(model);
    makeRouter(model);
    console.log(`CRUD para ${capitalizedModel} gerado com sucesso!`);
}

// Função para capitalizar o nome do modelo
function capitalize(name) {
    return name.charAt(0).toUpperCase() + name.slice(1);
}

// Gerar contantes
function makeConstants(model) {
    const content = `
    export const ${model.toUpperCase()} = {
    KEYS: ['name', 'active', 'actions'],
    TABLE: [
        {
            column: 'Nome',
            order: 'name'
        },
        {
            column: 'Status',
        },
        {
            column: 'Ações',
        }
    ],
    FORM: {
        id: 0,
        name: '',
    },
    FILTER: {
        inputs: [
            {
                name: 'name',
                label: 'Nome',
                col: '3',
                type: 'text',
                placeholder: 'Nome'
            },
        ],
        selects: []
    },
    ACTIONS: ['modal', 'delete']
}
    `
    saveFile(`src/constants/${pluralize(model)}.js`, content);
}

// Gerar view
function makeView(model) {
    const content = `
<template>
    <Layout>
        <PageHeader :title="titlePluralize"/>
        <b-card no-body>
            <Filter
                :session="session"
                :title="title"
                :titlePluralize="titlePluralize"
                :url="url"
                :button_modal="true"
                :filter="filter"
            />

            <TableLists
                :session="session"
                :url="url"
                :button_modal="false"
                :table="table"
                :keys="keys"
            >

                <template #change_active="{ value }">
                    <ChangeStatus
                        :value="value"
                        url="${pluralize(model)}/"
                    />
                </template>

                <template #actions="{ value }">

                    <Actions
                        :types="types"
                        :value="value"
                        :url="url"
                        :session="session"
                        @set-form-data="setFomData"
                    />
                </template>

            </TableLists>

            <ModalForm
                :session="session"
                :title="title"
                @submit-form="submitForm"
                @close-modal="closeModal"
            >

                <template v-slot:form-modal>

                     <BRow>
                        <div class="mb-3">
                            <label>Nome <span class="text-danger">*</span></label>
                            <BFormInput placeholder="Nome" required v-model="formData.name"/>
                        </div>
                    </BRow>

                </template>

            </ModalForm>
        </b-card>
    </Layout>
</template>

<script setup>
import PageHeader from "@/components/layouts/page-header.vue";
import TableLists from '@/components/base/TableLists.vue';
import Filter from '@/components/base/Filter.vue';
import Layout from "@/components/layouts/main.vue";
import ModalForm from "@/components/base/ModalForm.vue";
import ChangeStatus from "@/components/base/ChangeStatus.vue";
import Actions from "@/components/base/Actions.vue";
import {setSessions} from "@/components/composables/setSessions";
import {Forbidden} from "@/components/composables/functions";
import {insertORUpdate, resetModal, toFormData} from "@/components/composables/cruds";
import {ref, onMounted} from "vue";
import {${model.toUpperCase()}} from '@/constants/${pluralize(model)}';
import { useApiStore } from '@/stores'

const formData = ref(JSON.parse(JSON.stringify(${model.toUpperCase()}.FORM)));
const apiStore = useApiStore();

const titlePluralize = "usuários";
const title = "Usuário";
const session = "${capitalize(pluralize(model))}";
const url = "${pluralize(model)}/";

const filter = ${model.toUpperCase()}.FILTER;
const table = ${model.toUpperCase()}.TABLE
const keys = ${model.toUpperCase()}.KEYS;
const types = ${model.toUpperCase()}.ACTIONS;

function setFomData(payload) {
    formData.value = payload;
    // Loading será gerenciado nos services agora
}

async function closeModal() {
    await resetModal('form', 'save');
    formData.value = JSON.parse(JSON.stringify(${model.toUpperCase()}.FORM));
}

async function submitForm() {
    try {
        const newFormData = toFormData(formData.value);
        let endpoint = String(url.slice(0, -1));

        if (formData.value?.id > 0) {
            newFormData.append('_method', 'PUT')
            endpoint = url + formData.value.id
        }

        const result = await insertORUpdate(endpoint, newFormData, 'form', 'save', session);

        if (result) formData.value = JSON.parse(JSON.stringify(${model.toUpperCase()}.FORM));
    } catch (e) {
        console.error('Modal Form ${capitalize(pluralize(model))}: ', e);
        Forbidden(e);
    } finally {
        // Loading será gerenciado nos services agora
    }
}

onMounted(() => {
    setSessions('${capitalize(pluralize(model))}');
})

</script>
    `
    saveFile(`src/views/${pluralize(model)}/index.vue`, content);
}

// Append das sessions
function makeSession(model) {
    const filePath = 'src/components/composables/setSessions.js';

    const contentToAdd = `
    else if (session === '${capitalize(pluralize(model))}') {
        data = {
            url: '${pluralize(model)}',
            params: {
                page: 1,
                order_by: 'id',
                order: 'asc',
                limit: 25,
                name: '',
            }
        }
    }
    `;

    fs.appendFile(filePath, contentToAdd, (err) => {
        if (err) {
            console.error('Error while appending to the file:', err);
        } else {
            console.log('Content added successfully setSessions!');
        }
    });
}

// Append das rotas
function makeRouter(model) {
    const filePath = 'src/router/routes.js';

    const contentToAdd = `
    {
        path: "/usuarios",
        name: "${pluralize(model)}",
        meta: {
            title: "Usuários",
            authRequired: true,
        },
        component: () => import("@/views/${pluralize(model)}/index.vue"),
    },
    `;

    fs.appendFile(filePath, contentToAdd, (err) => {
        if (err) {
            console.error('Error while appending to the file:', err);
        } else {
            console.log('Content added successfully Router!');
        }
    });
}


// Função para salvar o arquivo
function saveFile(filePath, content) {
    const fullPath = path.resolve(__dirname, filePath);
    const dir = path.dirname(fullPath);

    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, {recursive: true});
    }

    fs.writeFileSync(fullPath, content.trim());
    console.log(`Arquivo ${filePath} gerado.`);
}

// Executar
const modelName = process.argv[2];
if (!modelName) {
    console.error('Por favor, forneça um nome para o modelo.');
    process.exit(1);
}

makeCrud(modelName);
