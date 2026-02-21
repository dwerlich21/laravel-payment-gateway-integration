<template>
  <div
    class="text-editor"
    :class="className"
  >
    <div class="editor-toolbar">
      <div class="toolbar-group">
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          :class="{ 'btn-secondary': isFormatActive('bold') }"
          title="Negrito"
          @click="execCommand('bold')"
        >
          <i class="ri-bold" />
        </button>
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          :class="{ 'btn-secondary': isFormatActive('italic') }"
          title="Itálico"
          @click="execCommand('italic')"
        >
          <i class="ri-italic" />
        </button>
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          :class="{ 'btn-secondary': isFormatActive('underline') }"
          title="Sublinhado"
          @click="execCommand('underline')"
        >
          <i class="ri-underline" />
        </button>
      </div>

      <div class="toolbar-group">
        <select
          ref="headingSelect"
          class="form-select form-select-sm"
          @change="changeHeading"
        >
          <option value="">
            Parágrafo
          </option>
          <option value="h1">
            Título 1
          </option>
          <option value="h2">
            Título 2
          </option>
          <option value="h3">
            Título 3
          </option>
          <option value="h4">
            Título 4
          </option>
        </select>
      </div>

      <div class="toolbar-group">
        <select
          ref="fontSizeSelect"
          class="form-select form-select-sm"
          @change="changeFontSize"
        >
          <option value="">
            Tamanho
          </option>
          <option value="10px">
            10px
          </option>
          <option value="12px">
            12px
          </option>
          <option value="14px">
            14px (padrão)
          </option>
          <option value="16px">
            16px
          </option>
          <option value="18px">
            18px
          </option>
          <option value="20px">
            20px
          </option>
          <option value="24px">
            24px
          </option>
          <option value="28px">
            28px
          </option>
          <option value="32px">
            32px
          </option>
          <option value="36px">
            36px
          </option>
        </select>
      </div>

      <div class="toolbar-group">
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Alinhar à esquerda"
          @click="execCommand('justifyLeft')"
        >
          <i class="ri-align-left" />
        </button>
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Centralizar"
          @click="execCommand('justifyCenter')"
        >
          <i class="ri-align-center" />
        </button>
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Alinhar à direita"
          @click="execCommand('justifyRight')"
        >
          <i class="ri-align-right" />
        </button>
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Justificar"
          @click="execCommand('justifyFull')"
        >
          <i class="ri-align-justify" />
        </button>
      </div>

      <div class="toolbar-group">
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Lista com marcadores"
          @click="execCommand('insertUnorderedList')"
        >
          <i class="ri-list-unordered" />
        </button>
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Lista numerada"
          @click="execCommand('insertOrderedList')"
        >
          <i class="ri-list-ordered" />
        </button>
      </div>

      <div class="toolbar-group">
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Inserir link"
          @click="insertLink"
        >
          <i class="ri-link" />
        </button>
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Remover link"
          @click="execCommand('unlink')"
        >
          <i class="ri-link-unlink" />
        </button>
      </div>

      <div class="toolbar-group">
        <input
          ref="imageInput"
          type="file"
          accept="image/*"
          style="display: none"
          @change="insertImage"
        >
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Inserir imagem"
          @click="$refs.imageInput.click()"
        >
          <i class="ri-image-line" />
        </button>
      </div>

      <div class="toolbar-group">
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Desfazer"
          @click="execCommand('undo')"
        >
          <i class="ri-arrow-go-back-line" />
        </button>
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Refazer"
          @click="execCommand('redo')"
        >
          <i class="ri-arrow-go-forward-line" />
        </button>
      </div>

      <div class="toolbar-group">
        <button
          type="button"
          class="btn btn-sm btn-soft-secondary"
          title="Inserir tabela"
          @click="insertTable"
        >
          <i class="ri-table-line" />
        </button>
      </div>
    </div>

    <div
      ref="editor"
      class="editor-content"
      contenteditable="true"
      @input="handleInput"
      @paste="handlePaste"
      @keydown="handleKeydown"
      @focus="handleFocus"
      @blur="handleBlur"
      @click="handleEditorClick"
      @keyup="updateHeadingSelect"
      @mouseup="updateHeadingSelect"
      @selectionchange="updateHeadingSelect"
    />

    <div
      v-if="showWordCount"
      class="editor-footer"
    >
      <small class="text-muted">
        Caracteres: {{ characterCount }} | Palavras: {{ wordCount }}
      </small>
    </div>
  </div>
</template>

<script>
import {ref, computed, watch, nextTick, onMounted} from 'vue'

export default {
    name: 'TextEditor',
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: 'Digite aqui...'
        },
        showWordCount: {
            type: Boolean,
            default: false
        },
        height: {
            type: String,
            default: '300px'
        },
        uploadUrl: {
            type: String,
            default: null
        },
        className: {
            type: String,
            required: false,
            default: ''
        }
    },
    emits: ['update:modelValue', 'focus', 'blur'],
    setup(props, {emit}) {
        const editor = ref(null)
        const imageInput = ref(null)
        const headingSelect = ref(null)
        const fontSizeSelect = ref(null)
        const isFocused = ref(false)
        const isUpdating = ref(false)

        const characterCount = computed(() => {
            return props.modelValue.replace(/<[^>]*>/g, '').length
        })

        const wordCount = computed(() => {
            const text = props.modelValue.replace(/<[^>]*>/g, '').trim()
            return text ? text.split(/\s+/).length : 0
        })

        // Funções para salvar e restaurar posição do cursor
        const saveCursorPosition = () => {
            const selection = window.getSelection()
            if (!selection.rangeCount) return null

            const range = selection.getRangeAt(0)
            const preCaretRange = range.cloneRange()
            preCaretRange.selectNodeContents(editor.value)
            preCaretRange.setEnd(range.startContainer, range.startOffset)

            return {
                start: preCaretRange.toString().length,
                end: preCaretRange.toString().length + range.toString().length
            }
        }

        const restoreCursorPosition = (savedPosition) => {
            if (!savedPosition || !editor.value) return

            const selection = window.getSelection()
            const range = document.createRange()

            let charIndex = 0
            let nodeStack = [editor.value]
            let node, foundStart = false, foundEnd = false

            while (node = nodeStack.pop()) {
                if (node.nodeType === Node.TEXT_NODE) {
                    const nextCharIndex = charIndex + node.textContent.length

                    if (!foundStart && savedPosition.start >= charIndex && savedPosition.start <= nextCharIndex) {
                        range.setStart(node, savedPosition.start - charIndex)
                        foundStart = true
                    }

                    if (foundStart && savedPosition.end >= charIndex && savedPosition.end <= nextCharIndex) {
                        range.setEnd(node, savedPosition.end - charIndex)
                        foundEnd = true
                        break
                    }

                    charIndex = nextCharIndex
                } else {
                    for (let i = node.childNodes.length - 1; i >= 0; i--) {
                        nodeStack.push(node.childNodes[i])
                    }
                }
            }

            if (foundStart) {
                selection.removeAllRanges()
                selection.addRange(range)
            }
        }

        const execCommand = (command, value = null) => {
            // Para comandos de formatação inline, usar execCommand nativo
            if (['bold', 'italic', 'underline', 'createLink', 'unlink', 'insertText', 'insertHTML', 'undo', 'redo'].includes(command)) {
                document.execCommand(command, false, value)
                editor.value.focus()
                updateContent()
            } else if (['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull', 'insertUnorderedList', 'insertOrderedList'].includes(command)) {
                // Para comandos de formatação de bloco, usar execCommand também
                document.execCommand(command, false, value)
                editor.value.focus()
                updateContent()
            } else {
                // Para outros comandos, usar execCommand padrão
                document.execCommand(command, false, value)
                editor.value.focus()
                updateContent()
            }
        }

        const isFormatActive = (command) => {
            try {
                return document.queryCommandState(command)
            } catch (e) {
                return false
            }
        }

        const updateHeadingSelect = () => {
            if (!headingSelect.value || !editor.value) return

            const selection = window.getSelection()
            if (!selection.rangeCount) return

            const range = selection.getRangeAt(0)
            let currentBlock = range.startContainer

            // Se é um nó de texto, pega o elemento pai
            if (currentBlock.nodeType === Node.TEXT_NODE) {
                currentBlock = currentBlock.parentNode
            }

            // Procura por um elemento de bloco válido
            while (currentBlock && currentBlock !== editor.value) {
                if (currentBlock.nodeType === Node.ELEMENT_NODE &&
                    ['DIV', 'P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(currentBlock.tagName)) {
                    break
                }
                currentBlock = currentBlock.parentNode
            }

            // Atualiza o select baseado no elemento atual
            if (currentBlock && currentBlock.tagName) {
                const tagName = currentBlock.tagName.toLowerCase()
                if (['h1', 'h2', 'h3', 'h4'].includes(tagName)) {
                    headingSelect.value.value = tagName
                } else {
                    headingSelect.value.value = ''
                }
            }
        }

        const findBlockElement = (node) => {
            while (node && node !== editor.value) {
                if (node.nodeType === Node.ELEMENT_NODE &&
                    ['DIV', 'P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(node.tagName)) {
                    return node
                }
                node = node.parentNode
            }
            return null
        }

        const changeHeading = (event) => {
            const value = event.target.value
            const selection = window.getSelection()

            if (!selection.rangeCount) {
                editor.value.focus()
                return
            }

            const range = selection.getRangeAt(0)

            // Se não há seleção (cursor apenas posicionado)
            if (range.collapsed) {
                let currentBlock = findBlockElement(range.startContainer)

                // Se não encontrou um bloco, procura criar um contexto
                if (!currentBlock) {
                    // Se o cursor está diretamente no editor, cria um novo bloco
                    if (range.startContainer === editor.value) {
                        const newElement = document.createElement(value || 'div')
                        newElement.innerHTML = '<br>'
                        range.insertNode(newElement)

                        // Posiciona cursor dentro do novo elemento
                        const newRange = document.createRange()
                        newRange.setStart(newElement, 0)
                        newRange.collapse(true)
                        selection.removeAllRanges()
                        selection.addRange(newRange)

                        updateContent()
                        return
                    }

                    // Se está dentro de texto mas sem bloco pai, envolve em um bloco
                    const textNode = range.startContainer
                    const newElement = document.createElement(value || 'div')

                    if (textNode.nodeType === Node.TEXT_NODE) {
                        const parent = textNode.parentNode
                        newElement.appendChild(textNode.cloneNode(true))
                        parent.replaceChild(newElement, textNode)
                    } else {
                        newElement.innerHTML = textNode.innerHTML || '<br>'
                        textNode.parentNode.replaceChild(newElement, textNode)
                    }

                    updateContent()
                    return
                }

                // Se encontrou um bloco, substitui por um novo com o tipo desejado
                const newElement = document.createElement(value || 'div')

                // Preserva o conteúdo existente
                if (currentBlock.innerHTML.trim()) {
                    newElement.innerHTML = currentBlock.innerHTML
                } else {
                    newElement.innerHTML = '<br>'
                }

                // Preserva classes se existirem
                if (currentBlock.className) {
                    newElement.className = currentBlock.className
                }

                // Calcula posição relativa do cursor dentro do bloco atual
                const blockRange = document.createRange()
                blockRange.selectNodeContents(currentBlock)
                blockRange.setEnd(range.startContainer, range.startOffset)
                const offsetInBlock = blockRange.toString().length

                // Substitui o elemento
                currentBlock.parentNode.replaceChild(newElement, currentBlock)

                // Restaura cursor na mesma posição relativa
                nextTick(() => {
                    let charIndex = 0
                    let walker = document.createTreeWalker(
                        newElement,
                        NodeFilter.SHOW_TEXT,
                        null,
                        false
                    )

                    let node
                    while (node = walker.nextNode()) {
                        const nextCharIndex = charIndex + node.textContent.length
                        if (offsetInBlock >= charIndex && offsetInBlock <= nextCharIndex) {
                            const newRange = document.createRange()
                            newRange.setStart(node, offsetInBlock - charIndex)
                            newRange.collapse(true)
                            selection.removeAllRanges()
                            selection.addRange(newRange)
                            break
                        }
                        charIndex = nextCharIndex
                    }

                    // Se não conseguiu posicionar, coloca no início do elemento
                    if (!selection.rangeCount) {
                        const newRange = document.createRange()
                        newRange.setStart(newElement, 0)
                        newRange.collapse(true)
                        selection.removeAllRanges()
                        selection.addRange(newRange)
                    }
                })

            } else {
                // Se há texto selecionado, trata diferente
                const startBlock = findBlockElement(range.startContainer)
                const endBlock = findBlockElement(range.endContainer)

                // Se a seleção está dentro de um único bloco
                if (startBlock && startBlock === endBlock) {
                    const newElement = document.createElement(value || 'div')
                    newElement.innerHTML = startBlock.innerHTML

                    if (startBlock.className) {
                        newElement.className = startBlock.className
                    }

                    startBlock.parentNode.replaceChild(newElement, startBlock)
                } else {
                    // Para seleções que abrangem múltiplos blocos, usa o comando padrão
                    // mas de forma mais controlada
                    try {
                        document.execCommand('formatBlock', false, value || 'div')
                    } catch (e) {
                        console.warn('Comando formatBlock falhou:', e)
                    }
                }
            }

            updateContent()
            editor.value.focus()

            // Atualiza o select após a mudança
            setTimeout(updateHeadingSelect, 10)
        }

        const changeFontSize = (event) => {
            const size = event.target.value
            const selection = window.getSelection()

            if (!selection.rangeCount) {
                editor.value.focus()
                return
            }

            const range = selection.getRangeAt(0)

            // Se há texto selecionado
            if (!range.collapsed) {
                const selectedContent = range.extractContents()
                const span = document.createElement('span')

                if (size) {
                    span.style.fontSize = size
                }

                span.appendChild(selectedContent)
                range.insertNode(span)

                // Seleciona o conteúdo formatado
                const newRange = document.createRange()
                newRange.selectNodeContents(span)
                selection.removeAllRanges()
                selection.addRange(newRange)

                updateContent()
            } else {
                // Se não há seleção, preparar para próxima digitação
                if (size) {
                    const span = document.createElement('span')
                    span.style.fontSize = size
                    span.innerHTML = '&#8203;' // Zero-width space para manter o span

                    range.insertNode(span)
                    range.setStart(span, 1)
                    range.collapse(true)
                    selection.removeAllRanges()
                    selection.addRange(range)
                }
            }

            editor.value.focus()
            updateContent()
        }

        const getCurrentFontSize = (element) => {
            const style = window.getComputedStyle(element)
            return parseInt(style.fontSize) || 14
        }

        const insertLink = () => {
            const url = prompt('Digite a URL do link:')
            if (url) {
                execCommand('createLink', url)
            }
        }

        const createImageWrapper = (imageSrc, width = 200, position = 'left') => {
            const wrapper = document.createElement('div')
            wrapper.className = 'image-wrapper'
            wrapper.style.cssText = `
        position: relative;
        display: inline-block;
        max-width: 100%;
        margin: 5px;
        float: ${position === 'center' ? 'none' : position};
        ${position === 'center' ? 'display: block; text-align: center; clear: both;' : ''}
        `

            const img = document.createElement('img')
            img.src = imageSrc
            img.style.cssText = `
        width: ${width}px;
        height: auto;
        max-width: 100%;
        border-radius: 4px;
        cursor: pointer;
        `
            img.setAttribute('draggable', 'false')

            // Container para os handles de redimensionamento
            const resizeContainer = document.createElement('div')
            resizeContainer.className = 'resize-container'
            resizeContainer.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: none;
        pointer-events: none;
        `

            // Handles de redimensionamento
            const handles = ['nw', 'ne', 'sw', 'se']
            handles.forEach(handle => {
                const handleEl = document.createElement('div')
                handleEl.className = `resize-handle resize-${handle}`
                handleEl.style.cssText = `
              position: absolute;
              width: 8px;
              height: 8px;
              background: #007bff;
              border: 1px solid white;
              border-radius: 50%;
              cursor: ${handle.includes('n') ? (handle.includes('w') ? 'nw' : 'ne') : (handle.includes('w') ? 'sw' : 'se')}-resize;
              pointer-events: all;
              z-index: 1000;
              ${handle.includes('n') ? 'top: -4px;' : 'bottom: -4px;'}
              ${handle.includes('w') ? 'left: -4px;' : 'right: -4px;'}
            `
                resizeContainer.appendChild(handleEl)
            })

            // Toolbar de posicionamento
            const toolbar = document.createElement('div')
            toolbar.className = 'image-toolbar'
            const toolbarLeft = position === 'center' ? '50%' : '0'
            const toolbarTransform = position === 'center' ? 'translateX(-50%)' : 'none'
            toolbar.style.cssText = `
            position: absolute;
            top: -35px;
            left: ${toolbarLeft};
            transform: ${toolbarTransform};
            background: rgba(0, 0, 0, 0.8);
            border-radius: 4px;
            padding: 4px;
            display: none;
            z-index: 1001;
            transition: left 0.2s ease, transform 0.2s ease;
            `

            const positions = [
                {icon: 'ri-align-left', title: 'Alinhar à esquerda', value: 'left'},
                {icon: 'ri-align-center', title: 'Centralizar', value: 'center'},
                {icon: 'ri-align-right', title: 'Alinhar à direita', value: 'right'}
            ]

            positions.forEach(pos => {
                const btn = document.createElement('button')
                btn.innerHTML = `<i class="${pos.icon}"></i>`
                btn.title = pos.title
                btn.style.cssText = `
              background: none;
              border: none;
              color: white;
              cursor: pointer;
              padding: 4px 6px;
              margin: 0 1px;
              border-radius: 3px;
              font-size: 14px;
              display: inline-flex;
              align-items: center;
              justify-content: center;
            `
                btn.onmouseover = () => {
                    btn.style.background = 'rgba(255, 255, 255, 0.2)'
                }
                btn.onmouseout = () => {
                    btn.style.background = 'none'
                }
                btn.onclick = (e) => {
                    e.preventDefault()
                    e.stopPropagation()
                    updateImagePosition(wrapper, pos.value)
                }
                toolbar.appendChild(btn)
            })

            // Botão de deletar
            const deleteBtn = document.createElement('button')
            deleteBtn.innerHTML = '<i class="ri-delete-bin-line"></i>'
            deleteBtn.title = 'Excluir imagem'
            deleteBtn.style.cssText = `
            background: #dc3545;
            border: none;
            color: white;
            cursor: pointer;
            padding: 4px 6px;
            margin-left: 6px;
            border-radius: 3px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            `
            deleteBtn.onmouseover = () => {
                deleteBtn.style.background = '#c82333'
            }
            deleteBtn.onmouseout = () => {
                deleteBtn.style.background = '#dc3545'
            }
            deleteBtn.onclick = (e) => {
                e.preventDefault()
                e.stopPropagation()
                wrapper.remove()
                updateContent()
            }
            toolbar.appendChild(deleteBtn)

            wrapper.appendChild(img)
            wrapper.appendChild(resizeContainer)
            wrapper.appendChild(toolbar)

            // Event listeners para seleção da imagem
            wrapper.addEventListener('click', (e) => {
                e.preventDefault()
                e.stopPropagation()
                selectImage(wrapper)
            })

            // Event listeners para redimensionamento
            handles.forEach(handle => {
                const handleEl = resizeContainer.querySelector(`.resize-${handle}`)
                handleEl.addEventListener('mousedown', (e) => {
                    e.preventDefault()
                    e.stopPropagation()
                    startResize(e, wrapper, img, handle)
                })
            })

            return wrapper
        }

        const updateImagePosition = (wrapper, position) => {
            const toolbar = wrapper.querySelector('.image-toolbar')

            wrapper.style.float = position === 'center' ? 'none' : position
            if (position === 'center') {
                wrapper.style.display = 'block'
                wrapper.style.textAlign = 'center'
                wrapper.style.clear = 'both'
                wrapper.style.margin = '10px auto'

                // Centraliza a toolbar também
                toolbar.style.left = '50%'
                toolbar.style.transform = 'translateX(-50%)'
            } else {
                wrapper.style.display = 'inline-block'
                wrapper.style.textAlign = 'left'
                wrapper.style.clear = 'none'
                wrapper.style.margin = '5px'

                // Restaura posicionamento normal da toolbar
                toolbar.style.left = '0'
                toolbar.style.transform = 'none'
            }
            updateContent()
        }

        const selectImage = (wrapper) => {
            // Remove seleção de outras imagens
            const allWrappers = editor.value.querySelectorAll('.image-wrapper')
            allWrappers.forEach(w => {
                w.querySelector('.resize-container').style.display = 'none'
                w.querySelector('.image-toolbar').style.display = 'none'
            })

            // Mostra controles da imagem selecionada
            wrapper.querySelector('.resize-container').style.display = 'block'
            wrapper.querySelector('.image-toolbar').style.display = 'block'
        }

        const startResize = (e, wrapper, img, handle) => {
            const startX = e.clientX
            const startY = e.clientY
            const startWidth = parseInt(img.style.width) || img.offsetWidth
            const startHeight = parseInt(img.style.height) || img.offsetHeight
            const aspectRatio = startWidth / startHeight

            const onMouseMove = (e) => {
                const deltaX = e.clientX - startX
                const deltaY = e.clientY - startY

                let newWidth = startWidth
                let newHeight = startHeight

                if (handle.includes('e')) {
                    newWidth = startWidth + deltaX
                } else if (handle.includes('w')) {
                    newWidth = startWidth - deltaX
                }

                // Mantém proporção
                newHeight = newWidth / aspectRatio

                // Limites mínimos e máximos
                newWidth = Math.max(50, Math.min(newWidth, editor.value.offsetWidth - 20))
                newHeight = newWidth / aspectRatio

                img.style.width = newWidth + 'px'
                img.style.height = newHeight + 'px'
            }

            const onMouseUp = () => {
                document.removeEventListener('mousemove', onMouseMove)
                document.removeEventListener('mouseup', onMouseUp)
                updateContent()
            }

            document.addEventListener('mousemove', onMouseMove)
            document.addEventListener('mouseup', onMouseUp)
        }

        // Array para armazenar IDs das imagens usadas
        const usedImageIds = ref([])

        const insertImage = async (event) => {
            const file = event.target.files[0]
            if (!file) return

            // Validações básicas
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
            if (!allowedTypes.includes(file.type)) {
                alert('Tipo de arquivo não permitido. Use JPEG, PNG, GIF ou WebP.')
                event.target.value = ''
                return
            }

            const maxSize = 5 * 1024 * 1024 // 5MB
            if (file.size > maxSize) {
                alert('Arquivo muito grande. Tamanho máximo: 5MB.')
                event.target.value = ''
                return
            }

            try {
                // Mostrar preview imediato com base64 enquanto faz upload
                const reader = new FileReader()
                reader.onload = (e) => {
                    const tempImageUrl = e.target.result
                    const imageWrapper = insertImageAtCursor(tempImageUrl)

                    // Verificar se o imageWrapper foi criado com sucesso
                    if (imageWrapper) {
                        // Adicionar indicador de upload
                        addUploadIndicator(imageWrapper)

                        // Fazer upload em background
                        uploadImageToServer(file, imageWrapper)
                    } else {
                        console.error('Erro ao inserir imagem no editor')
                        alert('Erro ao inserir imagem. Tente novamente.')
                    }
                }
                reader.readAsDataURL(file)

            } catch (error) {
                console.error('Erro ao processar imagem:', error)
                alert('Erro ao processar imagem. Tente novamente.')
            }

            // Limpar input
            event.target.value = ''
        }

        const uploadImageToServer = async (file, imageWrapper) => {
            try {
                const formData = new FormData()
                formData.append('image', file)

                // Fazer upload para a API
                const response = await fetch('/api/image-upload', {
                    method: 'POST',
                    body: formData,
                })

                if (!response.ok) {
                    throw new Error('Erro no upload')
                }

                const result = await response.json()

                if (result.success) {
                    // Substituir base64 pela URL do servidor
                    const img = imageWrapper.querySelector('img')
                    img.src = result.url
                    img.setAttribute('data-image-id', result.image_id)

                    // Remover indicador de upload
                    removeUploadIndicator(imageWrapper)

                    // Armazenar ID da imagem para marcar como usada depois
                    storeImageId(result.image_id)

                } else {
                    throw new Error(result.error || 'Erro no upload')
                }

            } catch (error) {
                console.error('Erro no upload:', error)

                // Remover indicador e mostrar erro
                removeUploadIndicator(imageWrapper)
                addErrorIndicator(imageWrapper, 'Erro no upload. Imagem salva localmente.')
            }
        }

        const addUploadIndicator = (imageWrapper) => {
            if (!imageWrapper) {
                console.error('imageWrapper não encontrado para adicionar indicador de upload')
                return
            }

            const indicator = document.createElement('div')
            indicator.className = 'upload-indicator'
            indicator.innerHTML = `
                <div class="upload-spinner">
                    <i class="ri-loader-4-line"></i>
                    <span>Enviando...</span>
                </div>
            `
            indicator.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 12px;
                border-radius: 4px;
                z-index: 1002;
            `

            const spinner = indicator.querySelector('.upload-spinner')
            spinner.style.cssText = `
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 4px;
            `

            const icon = indicator.querySelector('i')
            icon.style.cssText = `
                font-size: 24px;
                animation: spin 1s linear infinite;
            `

            imageWrapper.appendChild(indicator)
        }

        const removeUploadIndicator = (imageWrapper) => {
            if (!imageWrapper) {
                console.error('imageWrapper não encontrado para remover indicador de upload')
                return
            }

            const indicator = imageWrapper.querySelector('.upload-indicator')
            if (indicator) {
                indicator.remove()
            }
        }

        const addErrorIndicator = (imageWrapper, message) => {
            if (!imageWrapper) {
                console.error('imageWrapper não encontrado para adicionar indicador de erro')
                return
            }

            const indicator = document.createElement('div')
            indicator.className = 'error-indicator'
            indicator.innerHTML = `
                <div class="error-content">
                    <i class="ri-error-warning-line"></i>
                    <span>${message}</span>
                </div>
            `
            indicator.style.cssText = `
                position: absolute;
                bottom: -25px;
                left: 0;
                right: 0;
                background: #dc3545;
                color: white;
                padding: 4px 8px;
                font-size: 11px;
                border-radius: 4px;
                z-index: 1002;
            `

            imageWrapper.appendChild(indicator)

            // Remover após 5 segundos
            setTimeout(() => {
                if (indicator && indicator.parentNode) {
                    indicator.remove()
                }
            }, 5000)
        }

        const storeImageId = (imageId) => {
            if (!usedImageIds.value.includes(imageId)) {
                usedImageIds.value.push(imageId)
            }
        }

        // Função para marcar imagens como usadas (chamar ao salvar o conteúdo)
        const markImagesAsUsed = async () => {
            if (usedImageIds.value.length === 0) return

            try {
                const response = await fetch('/api/image-usage', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        image_ids: usedImageIds.value
                    })
                })

                if (response.ok) {
                    usedImageIds.value = [] // Limpar lista após marcar como usadas
                }
            } catch (error) {
                console.error('Erro ao marcar imagens como usadas:', error)
            }
        }

        const insertImageAtCursor = (imageSrc) => {
            const selection = window.getSelection()
            let imageWrapper

            if (!selection.rangeCount) {
                editor.value.focus()
                // Se não há seleção, inserir no final do editor
                imageWrapper = createImageWrapper(imageSrc, 200, 'left')
                editor.value.appendChild(imageWrapper)
            } else {
                const range = selection.getRangeAt(0)
                imageWrapper = createImageWrapper(imageSrc, 200, 'left')

                range.insertNode(imageWrapper)
                range.setStartAfter(imageWrapper)
                range.collapse(true)
                selection.removeAllRanges()
                selection.addRange(range)
            }

            updateContent()
            editor.value.focus()
            return imageWrapper
        }

        const insertTable = () => {
            const rows = prompt('Número de linhas:') || 3
            const cols = prompt('Número de colunas:') || 3

            let table = '<table class="table table-bordered"><tbody>'
            for (let i = 0; i < rows; i++) {
                table += '<tr>'
                for (let j = 0; j < cols; j++) {
                    table += '<td>&nbsp;</td>'
                }
                table += '</tr>'
            }
            table += '</tbody></table>'

            execCommand('insertHTML', table)
        }

        const handleInput = () => {
            if (!isUpdating.value) {
                updateContent()
            }
        }

        const handlePaste = (event) => {
            event.preventDefault()
            const text = event.clipboardData.getData('text/plain')
            execCommand('insertText', text)
        }

        const handleKeydown = (event) => {
            // Ctrl+B para negrito
            if (event.ctrlKey && event.key === 'b') {
                event.preventDefault()
                execCommand('bold')
            }
            // Ctrl+I para itálico
            if (event.ctrlKey && event.key === 'i') {
                event.preventDefault()
                execCommand('italic')
            }
            // Ctrl+U para sublinhado
            if (event.ctrlKey && event.key === 'u') {
                event.preventDefault()
                execCommand('underline')
            }
            // Ctrl+Z para desfazer
            if (event.ctrlKey && event.key === 'z' && !event.shiftKey) {
                event.preventDefault()
                execCommand('undo')
            }
            // Ctrl+Shift+Z para refazer
            if (event.ctrlKey && event.shiftKey && event.key === 'Z') {
                event.preventDefault()
                execCommand('redo')
            }
        }

        const handleFocus = () => {
            isFocused.value = true
            emit('focus')
            // Atualiza o select após um pequeno delay para garantir que o cursor esteja posicionado
            setTimeout(updateHeadingSelect, 10)
        }

        const handleEditorClick = (event) => {
            // Se clicou fora de qualquer imagem, deseleciona todas
            if (!event.target.closest('.image-wrapper')) {
                const allWrappers = editor.value.querySelectorAll('.image-wrapper')
                allWrappers.forEach(w => {
                    w.querySelector('.resize-container').style.display = 'none'
                    w.querySelector('.image-toolbar').style.display = 'none'
                })
            }
        }


        const handleBlur = () => {
            isFocused.value = false
            emit('blur')
        }

        const updateContent = () => {
            if (editor.value && !isUpdating.value) {
                emit('update:modelValue', editor.value.innerHTML)
            }
        }

        const setContent = (content) => {
            if (!editor.value || isUpdating.value) return

            // Só atualiza se o conteúdo realmente mudou e não foi uma mudança interna
            if (editor.value.innerHTML !== content) {
                isUpdating.value = true
                const cursorPosition = saveCursorPosition()

                editor.value.innerHTML = content

                nextTick(() => {
                    if (cursorPosition && isFocused.value) {
                        restoreCursorPosition(cursorPosition)
                    }
                    isUpdating.value = false
                })
            }
        }

        watch(() => props.modelValue, (newValue) => {
            if (!isUpdating.value) {
                setContent(newValue)
            }
        })

        onMounted(() => {
            if (editor.value) {
                editor.value.style.minHeight = props.height
                if (props.placeholder && !props.modelValue) {
                    editor.value.setAttribute('data-placeholder', props.placeholder)
                }
                setContent(props.modelValue)
            }
        })

        return {
            editor,
            imageInput,
            headingSelect,
            fontSizeSelect,
            isFocused,
            characterCount,
            wordCount,
            execCommand,
            isFormatActive,
            findBlockElement,
            updateHeadingSelect,
            changeHeading,
            changeFontSize,
            getCurrentFontSize,
            insertLink,
            createImageWrapper,
            updateImagePosition,
            selectImage,
            startResize,
            insertImage,
            insertImageAtCursor,
            insertTable,
            handleInput,
            handlePaste,
            handleKeydown,
            handleFocus,
            handleEditorClick,
            handleBlur
        }
    }
}
</script>

<style
    scoped
    lang="scss"
>
.text-editor {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: white;
}

.editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.75rem;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 0.375rem 0.375rem 0 0;
}

.toolbar-group {
    display: flex;
    gap: 0.25rem;
}

.toolbar-group .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.2;
}

.toolbar-group .btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.toolbar-group .form-select {
    min-width: 120px;
}

.editor-content {
    padding: 1rem;
    min-height: 200px;
    outline: none;
    line-height: 1.6;
    font-family: inherit;
    font-size: 0.875rem;
}

.editor-content:empty:before {
    content: attr(data-placeholder);
    color: #6c757d;
    font-style: italic;
}

.editor-content:focus {
    box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.5);
}

.editor-content table {
    width: 100%;
    margin: 1rem 0;
}

.editor-content table td {
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    min-width: 50px;
    min-height: 30px;
}

/* Estilos para o wrapper de imagens */
.editor-content .image-wrapper {
    position: relative;
    display: inline-block;
    max-width: 100%;
    margin: 5px;
}

.editor-content .image-wrapper img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    cursor: pointer;
    display: block;
}

.editor-content .image-wrapper.left {
    float: left;
    margin-right: 15px;
    margin-bottom: 10px;
}

.editor-content .image-wrapper.right {
    float: right;
    margin-left: 15px;
    margin-bottom: 10px;
}

.editor-content .image-wrapper.center {
    float: none;
    display: block;
    text-align: center;
    clear: both;
    margin: 15px auto;
}

/* Estilos para os handles de redimensionamento */
.editor-content .resize-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.editor-content .resize-handle {
    position: absolute;
    width: 8px;
    height: 8px;
    background: #007bff;
    border: 1px solid white;
    border-radius: 50%;
    pointer-events: all;
    z-index: 1000;
}

.editor-content .resize-nw {
    top: -4px;
    left: -4px;
    cursor: nw-resize;
}

.editor-content .resize-ne {
    top: -4px;
    right: -4px;
    cursor: ne-resize;
}

.editor-content .resize-sw {
    bottom: -4px;
    left: -4px;
    cursor: sw-resize;
}

.editor-content .resize-se {
    bottom: -4px;
    right: -4px;
    cursor: se-resize;
}

/* Estilos para a toolbar de imagem */
.editor-content .image-toolbar {
    position: absolute;
    top: -35px;
    left: 0;
    background: rgba(0, 0, 0, 0.8);
    border-radius: 4px;
    padding: 4px;
    z-index: 1001;
    white-space: nowrap;
    transition: left 0.2s ease, transform 0.2s ease;
}

.editor-content .image-toolbar button {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 4px 6px;
    margin: 0 1px;
    border-radius: 3px;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease;
}

.editor-content .image-toolbar button:hover {
    background: rgba(255, 255, 255, 0.2);
}

.editor-content .image-toolbar button.delete-btn {
    background: #dc3545;
    margin-left: 6px;
}

.editor-content .image-toolbar button.delete-btn:hover {
    background: #c82333;
}

/* Estilos para clearfix após imagens flutuantes */
.editor-content::after {
    content: "";
    display: table;
    clear: both;
}


.editor-content ul, .editor-content ol {
    padding-left: 2rem;
    margin: 0.5rem 0;
}

.editor-content blockquote {
    border-left: 4px solid #dee2e6;
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: #6c757d;
}

.editor-content h1, .editor-content h2, .editor-content h3, .editor-content h4 {
    margin: 1rem 0 0.5rem 0;
    font-weight: 600;
}

.editor-content h1 {
    font-size: 1.5rem;
}

.editor-content h2 {
    font-size: 1.25rem;
}

.editor-content h3 {
    font-size: 1.125rem;
}

.editor-content h4 {
    font-size: 1rem;
}

.editor-content a {
    color: #0d6efd;
    text-decoration: underline;
}

.editor-footer {
    padding: 0.5rem 1rem;
    border-top: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 0 0 0.375rem 0.375rem;
}

/* Responsividade */
@media (max-width: 768px) {
    .editor-toolbar {
        flex-direction: column;
        gap: 0.25rem;
    }

    .toolbar-group {
        justify-content: center;
    }

    .toolbar-group .form-select {
        min-width: 100px;
    }
}

/* Estilos de validação */
.editor-content.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.editor-content.is-valid {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

[data-layout-mode="dark"] {
    .text-editor {
        border: 1px solid #2a2f34;
        background: #262A2F;
    }

    .editor-toolbar {
        border-bottom: 1px solid #2a2f34;
        background: #262A2F;
    }

    .editor-footer {
        border-top: 1px solid #2a2f34;
        background: #262A2F;
    }

    .editor-content:focus {
        color: #ced4da;
        background-color: #262a2f;
        border-color: #33393f;
        outline: 0;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.15);
    }
}
</style>