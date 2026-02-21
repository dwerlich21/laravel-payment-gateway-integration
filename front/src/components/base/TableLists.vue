<!--eslint-disable no-mixed-spaces-and-tabs-->
<template>
    <BCardBody>
        <div class="table-responsive table-card">
            <table
                id="table"
                class="table align-middle mb-0 table-striped table-hover"
            >
                <thead>
                <tr>
                    <!--                            Gerar as colunas do cabeçalho das tables-->
                    <th
                        v-for="(thead, index) in table"
                        :key="'th-table-list' + index"
                        :class="{'text-center' : (index > 0 && table[0].column !== 'check' && table[0].column !== 'ID') || (index > 1 && ((table[0].column === 'check' || table[0].column === 'ID')))}"
                        :style="`width: ${thead.column === 'check' || thead.column === 'ID' ? '1%' : 'auto'}`"
                        class="text-nowrap"
                    >
                        <input
                            v-if="thead.column === 'check'"
                            id="selectAll"
                            type="checkbox"
                            class="form-check-input"
                            @change="selectAll"
                        >
                        <span v-else>{{ thead.column }}</span>
                        <span style="white-space: nowrap">
                            <i
                                v-if="thead.order"
                                class="las la-arrow-down pointer"
                                :class="thead.order === order_by && order ==='asc' ? 'active' : ''"
                                @click="newOrder(thead.order, 'asc')"
                            />
                            <i
                                v-if="thead.order"
                                class="las la-arrow-up pointer"
                                :class="thead.order === order_by && order ==='desc' ? 'active' : ''"
                                @click="newOrder(thead.order, 'desc')"
                            />
                        </span>
                    </th>
                </tr>
                </thead>

                <!--                        Se encontrar resultados da api gerar as linhas-->
                <tbody
                    v-if="listAll.message && Object.keys(listAll.message).length > 0"
                    style="overflow-y: hidden"
                >
                <tr
                    v-for="line in listAll.message"
                    :id="'line' + line.id"
                    :key="'tr-table-list' + line.id"
                >
                    <td
                        v-for="(k, tdIndex) in keys"
                        :key="'td-table-list' + tdIndex"
                        :class="{'text-center' : (tdIndex > 0 && table[0].column !== 'check' && table[0].column !== 'ID') || (tdIndex > 1 && ((table[0].column === 'check' || table[0].column === 'ID'))),
                       'text-nowrap': k === 'actions'
              }"
                    >
                        <slot
                            :name="k"
                            :value="line"
                        >
                            {{ k.indexOf('.') > -1 ? getNestedValue(line, k) : (line[k] || 'N/A') }}
                        </slot>
                    </td>
                </tr>
                </tbody>

                <!--                        Se não mensagem de nada encontrado-->
                <tbody v-else-if="listAll.message">
                <tr>
                    <td
                        :colspan="Object.keys(table).length"
                        class="text-center"
                    >
                        Nenhum resultado
                        encontrado
                    </td>
                </tr>
                </tbody>
            </table>

            <span
                id="spinnerTable"
                class="spinner spinner-border flex-shrink-0 mx-auto"
                role="status"
            >
                <span class="visually-hidden"/>
            </span>
        </div>
    </BCardBody>


    <BCardFooter class="border-top-0">
        <div class="align-items-center mt-xl-3 justify-content-start d-lg-flex">
            <div class="align-items-center d-flex text-muted mb-2 mb-lg-0">
                <div class="me-2">
                    <span>Exibir</span>
                </div>
                <div class="col-auto">
                    <select
                        id="limitFilter"
                        class="form-control form-select"
                        @change="setLimit"
                    >
                        <option value="25">
                            25
                        </option>
                        <option value="50">
                            50
                        </option>
                        <option value="100">
                            100
                        </option>
                        <option value="250">
                            250
                        </option>
                    </select>
                </div>
                <div class="ms-2">
                    <span> registros</span>
                </div>
            </div>
            <div class="flex-shrink-0 me-lg-auto ms-lg-4 mb-2 mb-lg-0">
                <div
                    v-if="start >= 0"
                    class="text-muted"
                >
                    Exibindo de
                    <span class="fw-semibold">{{ start }}</span>
                    a
                    <span
                        class="fw-semibold"
                    >{{
                            partial
                     }}
                    </span>
                    de
                    <span class="fw-semibold">{{ total }}</span>
                    resultados
                </div>
            </div>


            <Pagination
                ref="paginationComponent"
                :session="props.session"
                @load-list="loadList"
            />
        </div>
    </BCardFooter>
</template>

<script setup>
import {getUrl, loadTable} from "@/composables/functions";
import {setSessions} from "@/composables/setSessions";
import Pagination from "@/components/base/Pagination.vue";
import {computed, onMounted, ref, defineProps, defineEmits} from "vue";

const props = defineProps({
    session: {
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
    changeList: {
        required: false,
        type: Boolean,
        default: false
    },
    button_modal: {
        required: false,
        type: Boolean,
        default: true
    },
})

const emits = defineEmits(['update-show-card']);

const order = ref(null);
const order_by = ref(null);

const listAll = computed(() => apiStore.listAll)
const start = computed(() => apiStore.listAll.start)
const partial = computed(() => apiStore.listAll.partial)
const total = computed(() => apiStore.listAll.count)

function setOrders() {
    const objData = JSON.parse(localStorage.getItem(props.session))

    if (!objData) {
        setTimeout(() => {
            setOrders();
        }, 300)
    } else {
        order.value = objData.params.order;
        order_by.value = objData.params.order_by;
    }
}

function getNestedValue(obj, path) {
    return path
        .split('.')
        .reduce((acc, key) => (acc && acc[key] !== undefined ? acc[key] : undefined), obj) || 'N/A';
}

function newOrder(new_order_by, new_order) {
    let obj = JSON.parse(localStorage.getItem(props.session))

    obj.params.order = new_order;
    order.value = new_order;

    obj.params.order_by = new_order_by;
    order_by.value = new_order_by;

    localStorage.setItem(props.session, JSON.stringify(obj));

    const url = getUrl(props.session)
    apiStore.getApi(url);

}

// Busca dos dados da tabela na store
function loadList() {
    if (props.session) {
        setSessions(props.session);
        const url = getUrl(props.session);
        apiStore.getApi(url)
    } else {
        setTimeout(() => {
            loadList();
        }, 300)
    }
}

// Settar novo valor de limit na localStorage e chamando nova busca na store
function setLimit() {
    let obj = JSON.parse(localStorage.getItem(props.session));
    obj.params.index = 0;
    obj.params.limit = document.getElementById('limitFilter').value;
    localStorage.setItem(props.session, JSON.stringify(obj));
    loadTable();

    const url = getUrl(props.session);
    apiStore.getApi(url);
}

function selectAll() {
    // Seleciona o elemento <tbody>
    const tbody = document.querySelector("tbody");
    const check = document.getElementById('selectAll').checked;

// Seleciona todos os checkboxes dentro do <tbody>
    const checkboxes = tbody.querySelectorAll('input[type="checkbox"]');

// Itera sobre os checkboxes e faz algo com cada um deles (exemplo: logar no console)
    checkboxes.forEach(checkbox => {
        checkbox.checked = check; // Retorna se está marcado ou não
    });

    emits('update-show-card', check)
}


onMounted(() => {
    // chamada de busca de dados na store
    loadList();
    setOrders();
})

</script>

<style>

th i {
    opacity: .4 !important;
}

th i.active {
    opacity: 1 !important;
}

.icon-list {
    border: solid 1px #C6C6C6;
    border-radius: 5px;
}


</style>
