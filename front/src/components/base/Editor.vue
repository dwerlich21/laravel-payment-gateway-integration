<template>
  <div class="col-12">
    <div class="mb-2 flex flex-wrap items-center gap-2">
      <button
        type="button"
        class="btn btn-light btn-sm"
        aria-label="Bold"
        @click="handleFormat('bold')"
      >
        <i class="mdi mdi-format-bold fs-5" />
      </button>
      <button
        type="button"
        class="btn btn-light btn-sm ms-1"
        aria-label="Italic"
        @click="handleFormat('italic')"
      >
        <i class="mdi mdi-format-italic fs-5" />
      </button>
      <button
        type="button"
        class="btn btn-light btn-sm ms-1"
        aria-label="Underline"
        @click="handleFormat('underline')"
      >
        <i class="mdi mdi-format-underline fs-5" />
      </button>
      <button
        type="button"
        class="btn btn-light btn-sm ms-1"
        aria-label="Align Left"
        @click="handleFormat('justifyLeft')"
      >
        <i class="mdi mdi-format-align-left fs-5" />
      </button>
      <button
        type="button"
        class="btn btn-light btn-sm ms-1"
        aria-label="Align Center"
        @click="handleFormat('justifyCenter')"
      >
        <i class="mdi mdi-format-align-center fs-5" />
      </button>
      <button
        type="button"
        class="btn btn-light btn-sm ms-1"
        aria-label="Align Right"
        @click="handleFormat('justifyRight')"
      >
        <i class="mdi mdi-format-align-right fs-5" />
      </button>
      <button
        type="button"
        class="btn btn-light btn-sm ms-1"
        aria-label="Insert Image"
        @click="handleInsertImage"
      >
        <i class="mdi mdi-image fs-5" />
      </button>
      <button
        type="button"
        class="btn btn-light btn-sm ms-1"
        aria-label="Insert Link"
        @click="handleInsertLink"
      >
        <i class="mdi mdi-link-variant fs-5" />
      </button>
      <button
        type="button"
        class="btn btn-light btn-sm ms-1"
        aria-label="Undo"
        @click="handleFormat('undo')"
      >
        <i class="mdi mdi-undo fs-5" />
      </button>
      <button
        type="button"
        class="btn btn-light btn-sm ms-1"
        aria-label="Redo"
        @click="handleFormat('redo')"
      >
        <i class="mdi mdi-redo fs-5" />
      </button>
      <!--            <select @change="handleFormat('fontName', $event.target.value)" class="p-2 rounded bg-gray-100 dark:bg-gray-700">-->
      <!--                <option value="Arial">Arial</option>-->
      <!--                <option value="Helvetica">Helvetica</option>-->
      <!--                <option value="Times New Roman">Times New Roman</option>-->
      <!--                <option value="Courier">Courier</option>-->
      <!--            </select>-->
      <button
        type="button"
        class="btn btn-light btn-sm ms-1"
        aria-label="Text Color"
        @click="handleFormat('foreColor')"
      >
        <i class="mdi mdi-invert-colors fs-5" />
      </button>
    </div>

    <div
      ref="editorRef"
      contenteditable="true"
      class="form-control mb-3"
      style="min-height: 200px"
      @input="onInput"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const text = ref('');
const wordCount = ref(0);
const activeButtons = ref({});
const editorRef = ref(null);

watch(text, (newVal) => {
    const words = newVal.trim().split(/\s+/);
    wordCount.value = words.length;
});

const handleFormat = (command, value = null) => {
    document.execCommand(command, false, value);
    editorRef.value.focus();
    activeButtons.value[command] = !activeButtons.value[command];
};

const handleInsertImage = () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.onchange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = document.createElement('img');
                img.src = event.target.result;
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                document.execCommand('insertHTML', false, img.outerHTML);
                text.value = editorRef.value.innerHTML;
            };
            reader.readAsDataURL(file);
        }
    };
    input.click();
};

const handleInsertLink = () => {
    const url = prompt('Enter URL:');
    const linkText = prompt('Enter link text:');
    if (url && linkText) {
        document.execCommand('insertHTML', false, `<a href="${url}" target="_blank">${linkText}</a>`);
    }
};

// const getButtonClass = (command) => {
//     return `p-2 rounded fa-solid fa-${command}`;
// };

const onInput = (e) => {
    text.value = e.target.innerHTML;
};

onMounted(() => {
    text.value = editorRef.value.innerHTML;
});
</script>

<style scoped>
.resize-handle {
    position: absolute;
    width: 10px;
    height: 10px;
    background-color: blue;
    border-radius: 50%;
}
</style>
