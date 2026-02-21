<script setup>
import {computed, defineProps} from "vue";
import {loadTable} from "@/composables/functions.js";

const props = defineProps({
    session: {
        required: true,
        type: String
    },
    page: {
        type: Number,
        required: true,
    },
    totalPages: {
        type: Number,
        required: true,
    },
})

const emit = defineEmits(['load-list']);

const apiStore = {};

const pages = computed(() => {
    let arr = [1];
    if (!isNaN(props.totalPages)) {
        arr = [];
        if (props.totalPages <= 1) return [1];

        if (props.totalPages < 5) {
            for (let i = 1; i <= props.totalPages; i++) {
                arr.push(i)
            }
        } else {
            if (props.page > 3) {
                arr.push('...');
            }

            if (props.page < 3) {
                for (let i = 1; i <= 5; i++) {
                    arr.push(i)
                }
            } else if (props.page >= 3 && props.page < props.totalPages - 1) {
                for (let i = props.page - 2; i <= props.page + 2; i++) {
                    arr.push(i)
                }
            } else if (props.page >= props.totalPages - 1) {
                for (let i = props.totalPages - 4; i <= props.totalPages; i++) {
                    arr.push(i)
                }
            }


            if (props.page < props.totalPages - 2) {
                arr.push('...');
            }
        }
    }

    return arr;
})

async function addValue() {
    if (props.page < props.totalPages) {
        loadTable();
        let obj = JSON.parse(localStorage.getItem(props.session));
        obj.params.page++;
        localStorage.setItem(props.session, JSON.stringify(obj));
        emit('load-list')
        setTimeout(() => {
            scroll();
        }, 50)
    }
}

async function subtractValue() {
    if (props.page > 1) {
        loadTable();
        let obj = JSON.parse(localStorage.getItem(props.session));
        obj.params.page--;
        localStorage.setItem(props.session, JSON.stringify(obj));
        emit('load-list')
        setTimeout(() => {
            scroll();
        }, 50)
    }
}

async function setValue(value) {
    if (value !== '...') {
        loadTable();
        let obj = JSON.parse(localStorage.getItem(props.session));
        obj.params.page = value;
        localStorage.setItem(props.session, JSON.stringify(obj));
        emit('load-list')
        setTimeout(() => {
            scroll();
        }, 50)
    }
}

function scroll() {
    const elemento = document.getElementById('limitFilter');
    const altura = elemento.offsetHeight;
    window.scrollTo(0, altura + 50);
}

</script>
<template>
    <ul
        id="pagination"
        class="pagination pagination-separated mb-0 float-end"
        style="top: auto"
    >
        <li
            class="page-item"
            :class="page === 1 ? 'disabled' : 'pointer'"
            @click="subtractValue"
        >
            <a class="page-link">Anterior</a>
        </li>
        <li
            v-for="p in pages"
            :key="`page-pagination-${p}`"
            :class="p === page ? 'active' : ''"
            class="page-item pointer"
            @click="setValue(p)"
        >
            <a class="page-link">{{ p }}</a>
        </li>
        <li
            class="page-item"
            :class="page === totalPages || (page === 1 && totalPages === 0 ) ? 'disabled' : 'pointer'"
            @click="addValue"
        >
            <a class="page-link">Próximo</a>
        </li>
    </ul>
</template>
