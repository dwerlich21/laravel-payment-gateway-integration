<template>
  <div class="mb-4 card-border">
    <!-- Cabeçalho do componente -->
    <BCardHeader class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        {{ title }}
      </h5>
      <BButton
        variant="soft-primary"
        size="sm"
        class="py-1"
        :disabled="isAddDisabled"
        @click="addNewDocument"
      >
        <i class="ri-add-line align-bottom" />
        Adicionar Documento
      </BButton>
    </BCardHeader>

    <BCardBody>
      <div
        v-if="true"
        class="small text-muted mb-2"
      >
        Total: {{ documents.length }} | Existentes: {{ existingDocsCount }} | Novos: {{ newDocsCount }}
      </div>

      <div
        v-if="documents.length === 0"
        class="text-center py-4"
      >
        <div class="mb-3">
          <i
            class="ri-file-list-3-line text-muted"
            style="font-size: 3rem;"
          />
        </div>
        <p class="text-muted mb-0">
          Nenhum documento cadastrado. Clique em "Adicionar Documento" para começar.
        </p>
      </div>

      <div v-else>
        <transition-group
          name="document-list"
          tag="div"
        >
          <BCard
            v-for="(doc, index) in documents"
            :key="getDocumentKey(doc, index)"
            class="mb-3 border document-item"
            no-body
          >
            <BCardBody class="p-3">
              <BRow>
                <BCol
                  lg="4"
                  md="6"
                  class="mb-3 mb-md-0"
                >
                  <div class="d-flex align-items-center h-100">
                    <div
                      v-if="doc.file || doc.file_url"
                      class="document-preview me-3"
                    >
                      <a
                        :href="doc.file_url"
                        target="_blank"
                      >
                        <div
                          v-if="isImage(doc.file_type)"
                          class="img-preview"
                        >
                          <img
                            :src="doc.file_url"
                            class="img-fluid rounded"
                            alt="Preview"
                            @error="($event) => $event.target.style.display = 'none'"
                          >
                        </div>
                        <div
                          v-else
                          class="file-icon"
                        >
                          <i
                            :class="getFileIcon(doc.file_type)"
                            class="fs-1"
                          />
                          <div class="file-ext">{{
                            doc.file_type
                          }}
                          </div>
                        </div>
                      </a>
                    </div>
                    <div class="document-info flex-grow-1">
                      <h6 class="mb-1 text-truncate">
                        {{ doc.name || 'Documento sem nome' }}
                      </h6>
                      <p
                        v-if="doc.file || doc.file_url"
                        class="small text-muted mb-0"
                      >
                        {{ formatFileSize(doc.file_size) }} -
                        {{ formatDate(doc.upload_date) }}
                      </p>
                      <div
                        v-if="doc.file"
                        class="small text-success"
                      >
                        <i class="ri-check-line me-1" />
                        Arquivo selecionado
                      </div>

                      <span
                        v-if="isNewDocument(doc)"
                        class="badge bg-soft-primary text-primary me-1"
                      >
                        Novo
                      </span>
                      <span
                        v-else
                        class="badge bg-soft-success text-success me-1"
                      >
                        Salvo
                      </span>
                    </div>
                  </div>
                </BCol>


                <BCol
                  lg="7"
                  md="5"
                >
                  <BRow>
                    <BCol
                      md="12"
                      class="mb-2"
                    >
                      <label class="form-label small">Nome do Documento</label>
                      <BFormInput
                        v-model="doc.name"
                        placeholder="Nome do documento"
                        @input="onDocumentFieldChange"
                      />
                    </BCol>
                    <BCol md="12">
                      <label class="form-label small">Tipo de Documento</label>
                      <Multiselect
                        v-model="doc.document_type"
                        mode="single"
                        :close-on-select="true"
                        :searchable="true"
                        :create-option="false"
                        :options="documentTypes"
                        :class="doc.document_type ? 'is-valid' :'is-invalid'"
                        placeholder="Selecione"
                        :name="`document_type_${index}`"
                        class="form-multiselect-sm"
                        @change="onDocumentFieldChange"
                      />
                    </BCol>
                  </BRow>
                </BCol>

                <BCol
                  lg="1"
                  md="1"
                  class="d-flex justify-content-end align-items-center"
                >
                  <div class="document-actions">
                    <BButton
                      v-if="!doc.file && !doc.file_url"
                      v-b-tooltip.hover
                      variant="soft-primary"
                      size="sm"
                      class="me-1"
                      title="Anexar arquivo"
                      @click="triggerFileInput(index)"
                    >
                      <i class="ri-attachment-2 fs-5" />
                    </BButton>
                    <BButton
                      v-b-tooltip.hover
                      variant="soft-danger"
                      size="sm"
                      title="Remover documento"
                      @click="removeDocument(index)"
                    >
                      <i class="ri-delete-bin-line fs-5" />
                    </BButton>
                    <input
                      :id="`document-file-${index}`"
                      :accept="acceptedFileTypes"
                      type="file"
                      class="d-none"
                      @change="handleFileSelect($event, index)"
                    >
                  </div>
                </BCol>
              </BRow>

              <BRow
                v-if="doc.file || doc.file_url"
                class="mt-3"
              >
                <BCol md="12">
                  <div class="d-flex align-items-center file-info p-2 rounded bg-light">
                    <i class="ri-file-text-line me-2" />
                    <span class="text-truncate">
                      {{ getFileName(doc.file) || getFileName(doc.file_url) }}
                    </span>
                    <span
                      v-if="doc.file"
                      class="badge bg-soft-success text-success ms-2"
                    >Novo arquivo
                    </span>
                    <BButton
                      v-b-tooltip.hover
                      variant="link"
                      size="sm"
                      class="ms-auto p-0"
                      title="Remover arquivo"
                      @click="removeFile(index)"
                    >
                      <i class="ri-close-line text-danger" />
                    </BButton>
                  </div>
                </BCol>
              </BRow>
            </BCardBody>
          </BCard>
        </transition-group>
      </div>
    </BCardBody>
  </div>
</template>

<script setup>
import {ref, defineProps, defineEmits, computed, watch, defineExpose, nextTick} from 'vue';
import Multiselect from '@vueform/multiselect';
import {notifyError} from "@/composables/messages";

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    title: {
        type: String,
        default: 'Documentos'
    },
    documentTypes: {
        type: Array,
        default: () => []
    },
    acceptedFileTypes: {
        type: String,
        default: ".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx"
    },
    maxFileSize: {
        type: Number,
        default: 5 * 1024 * 1024
    },
    maxDocuments: {
        type: Number,
        default: null
    }
});

const emit = defineEmits(['update:modelValue', 'document-added', 'document-removed', 'file-added', 'file-removed', 'error']);

const removedDocumentIds = ref(new Set());
const documents = ref([]);
const isSyncing = ref(false);
const lastEmittedData = ref(null);

const isNewDocument = (doc) => {
    return !doc.id || typeof doc.id === 'string' || doc.id < 0;
};

const getDocumentKey = (doc, index) => {
    if (doc.id && typeof doc.id === 'number') {
        return `existing-${doc.id}`;
    }
    return doc.tempId || `temp-${index}`;
};

const existingDocsCount = computed(() => {
    return documents.value.filter(doc => !isNewDocument(doc)).length;
});

const newDocsCount = computed(() => {
    return documents.value.filter(doc => isNewDocument(doc)).length;
});

watch(() => props.modelValue, (newValue) => {
    if (isSyncing.value) {
        return;
    }

    if (!newValue || newValue.length === 0) {
        documents.value = [];
        return;
    }

    const currentDataString = JSON.stringify(documents.value);
    const newDataString = JSON.stringify(newValue);

    if (currentDataString === newDataString) {
        return;
    }

    const existingDocs = newValue.filter(doc =>
        doc.id &&
        typeof doc.id === 'number' &&
        !removedDocumentIds.value.has(doc.id)
    );
    const currentNewDocs = documents.value.filter(doc => isNewDocument(doc));

    documents.value = [...existingDocs, ...currentNewDocs];

}, {deep: true, immediate: true});

const clearRemovedDocuments = () => {
    removedDocumentIds.value.clear();
};

const emitDocumentsUpdate = () => {
    if (isSyncing.value) {
        return;
    }

    const docsToEmit = documents.value.filter(doc => {
        if (!isNewDocument(doc)) return true;

        return doc.name?.trim() || doc.file || doc.document_type;
    });

    const dataToEmit = [...docsToEmit];
    const dataString = JSON.stringify(dataToEmit);

    if (lastEmittedData.value === dataString) {
        return;
    }

    isSyncing.value = true;
    lastEmittedData.value = dataString;

    emit('update:modelValue', dataToEmit);

    nextTick(() => {
        isSyncing.value = false;
    });
};

const onDocumentFieldChange = () => {
    setTimeout(() => {
        emitDocumentsUpdate();
    }, 100);
};

const isImage = (fileType) => {
    const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    return imageTypes.includes(fileType?.toLowerCase());
};

const getFileIcon = (fileType) => {
    if (!fileType) return 'ri-file-line';

    const fileType_lower = fileType.toLowerCase();

    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileType_lower)) {
        return 'ri-image-line';
    } else if (['pdf'].includes(fileType_lower)) {
        return 'ri-file-pdf-line';
    } else if (['doc', 'docx'].includes(fileType_lower)) {
        return 'ri-file-word-line';
    } else if (['xls', 'xlsx'].includes(fileType_lower)) {
        return 'ri-file-excel-line';
    } else if (['ppt', 'pptx'].includes(fileType_lower)) {
        return 'ri-file-ppt-line';
    } else if (['zip', 'rar', '7z'].includes(fileType_lower)) {
        return 'ri-file-zip-line';
    } else {
        return 'ri-file-text-line';
    }
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 Bytes';

    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${parseFloat((bytes / Math.pow(1024, i)).toFixed(2))} ${sizes[i]}`;
};

const formatDate = (date) => {
    if (!date) return '';

    const dateObj = typeof date === 'string' ? new Date(date) : date;

    return dateObj.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const getFileName = (file) => {
    if (!file) return '';

    if (file instanceof File) {
        return file.name;
    }

    if (typeof file === 'string') {
        const urlParts = file.split('/');
        return urlParts[urlParts.length - 1];
    }

    return 'arquivo';
};

const addNewDocument = () => {
    if (props.maxDocuments && documents.value.length >= props.maxDocuments) {
        notifyError(`Você atingiu o limite máximo de ${props.maxDocuments} documentos.`);
        emit('error', {type: 'max_documents', message: `Limite máximo de ${props.maxDocuments} documentos atingido.`});
        return;
    }

    const tempId = `temp-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    const newDoc = {
        tempId: tempId,
        name: '',
        document_type: null,
        file: null,
        file_type: null,
        file_size: null,
        file_url: null,
        upload_date: new Date(),
        _isNew: true
    };

    documents.value.push(newDoc);
    emit('document-added', newDoc);

};

const removeDocument = (index) => {
    if (index < 0 || index >= documents.value.length) {
        console.warn('❌ Índice inválido para remoção:', index);
        return;
    }

    const docToRemove = documents.value[index];

    if (!isNewDocument(docToRemove)) {
        removedDocumentIds.value.add(docToRemove.id);
    }

    if (docToRemove.file_url && docToRemove.file_url.startsWith('blob:')) {
        URL.revokeObjectURL(docToRemove.file_url);
    }

    documents.value.splice(index, 1);

    emit('document-removed', docToRemove);

    if (!isNewDocument(docToRemove) || docToRemove.name?.trim() || docToRemove.file || docToRemove.document_type) {
        emitDocumentsUpdate();
    }
};

const triggerFileInput = (index) => {
    const input = document.getElementById(`document-file-${index}`);
    if (input) {
        input.click();
    }
};

const handleFileSelect = async (event, index) => {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > props.maxFileSize) {
        notifyError(`O arquivo excede o tamanho máximo de ${formatFileSize(props.maxFileSize)}.`);
        emit('error', {
            type: 'max_file_size',
            message: `Arquivo excede tamanho máximo de ${formatFileSize(props.maxFileSize)}.`
        });
        event.target.value = '';
        return;
    }

    const fileExtension = file.name.split('.').pop()?.toLowerCase();

    try {
        const currentDoc = documents.value[index];

        if (currentDoc.file_url && currentDoc.file_url.startsWith('blob:')) {
            URL.revokeObjectURL(currentDoc.file_url);
        }

        const fileUrl = URL.createObjectURL(file);

        Object.assign(documents.value[index], {
            file: file,
            file_url: fileUrl,
            file_type: fileExtension,
            file_size: file.size,
            upload_date: new Date(),
            name: currentDoc.name || file.name.replace(`.${fileExtension}`, '')
        });

        await nextTick();

        emit('file-added', {
            document: documents.value[index],
            index,
            file: file
        });

        emitDocumentsUpdate();

    } catch (error) {
        console.error('❌ Erro ao processar arquivo:', error);
        notifyError('Erro ao processar arquivo selecionado.');
    }

    event.target.value = '';
};

const removeFile = (index) => {
    const currentDoc = documents.value[index];

    if (currentDoc.file_url && currentDoc.file_url.startsWith('blob:')) {
        URL.revokeObjectURL(currentDoc.file_url);
    }

    Object.assign(documents.value[index], {
        file: null,
        file_url: null,
        file_type: null,
        file_size: null
    });

    emit('file-removed', {
        document: documents.value[index],
        index
    });

    emitDocumentsUpdate();
};

const isAddDisabled = computed(() => {
    return props.maxDocuments && documents.value.length >= props.maxDocuments;
});

const hasFiles = computed(() => {
    return documents.value.some(doc => doc.file);
});

const getDocumentsData = () => {
    return documents.value;
};

defineExpose({
    addNewDocument,
    removeDocument,
    removeFile,
    getDocumentsData,
    hasFiles,
    clearRemovedDocuments
});
</script>

<style scoped>
.document-preview {
    width: 150px;
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.img-preview {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.img-preview img {
    object-fit: cover;
    width: 100%;
    height: 100%;
}

.file-icon {
    position: relative;
    text-align: center;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.file-ext {
    font-size: 10px;
    text-align: center;
    text-transform: uppercase;
    margin-top: -10px;
}

.document-list-enter-active,
.document-list-leave-active {
    transition: all 0.3s;
}

.document-list-enter-from,
.document-list-leave-to {
    opacity: 0;
    transform: translateY(-20px);
}

.document-item {
    transition: all 0.3s;
}

.file-info {
    font-size: 0.875rem;
    background-color: rgba(0, 0, 0, 0.03);
}
</style>