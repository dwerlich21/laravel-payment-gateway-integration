<!--eslint-disable no-mixed-spaces-and-tabs-->
<template>
    <Layout>
        <PageHeader :title="title"/>

        <BRow>
            <BCol
                xxl="12"
                class="mb-3"
            >
                <BCard no-body>
                    <BCardHeader class="border border-dashed border-end-0 border-start-0 border-top-0">
                        <div class="d-md-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">
                                {{ titleHeader }}
                            </h5>
                        </div>
                    </BCardHeader>

                    <BCardBody>
                        <slot name="form"/>

                        <ButtonsFormActions
                            :is-editing="isEditing"
                            :is-submitting="isSubmitting"
                            @submit="emits('submit-form');"
                        />
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>
    </Layout>
</template>

<script setup>
import {defineProps, defineEmits} from "vue";
import Layout from "@/components/layouts/main.vue";
import PageHeader from "@/components/layouts/page-header.vue";
import ButtonsFormActions from "@/components/base/ButtonsFormActions.vue";

defineProps({
    title: {
        type: String,
        required: true,
    },
    titleHeader: {
        type: String,
        required: true,
    },
    isEditing: {
        type: Boolean,
        default: false,
    },
    isSubmitting: {
        type: Boolean,
        default: false,
    }
});

const emits = defineEmits(['submit-form']);

</script>
