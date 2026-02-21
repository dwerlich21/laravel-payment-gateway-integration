<template>
  <div
    class="chat-ai-wrapper"
    :class="{ 'chat-ai-open': isOpen }"
  >
    <!-- Botão flutuante para abrir/fechar o chat -->
    <button
      v-b-tooltip.hover="'Assistente IA'"
      class="chat-ai-toggle"
      :class="{ 'pulse': hasNewMessage && !isOpen }"
      @click="toggleChat"
    >
      <i
        class="bx"
        :class="isOpen ? 'bx-x' : 'bx-bot'"
      />
      <span
        v-if="hasNewMessage && !isOpen"
        class="badge-notification"
      />
    </button>

    <!-- Janela de chat -->
    <div
      v-show="isOpen"
      class="chat-ai-window"
    >
      <div class="chat-ai-header">
        <div class="chat-ai-title">
          <i class="bx bx-bot mr-2" />
          <span>Assistente IA</span>
        </div>
        <div class="chat-ai-actions">
          <button
            v-b-tooltip.hover="'Limpar conversa'"
            class="chat-ai-action"
            @click="clearChat"
          >
            <i class="bx bx-trash" />
          </button>
        </div>
      </div>

      <div
        ref="messagesContainer"
        class="chat-ai-messages"
      >
        <div
          v-for="(message, index) in messages"
          :key="index"
          class="chat-ai-message"
          :class="{ 'chat-ai-message-user': message.type === 'user', 'chat-ai-message-ai': message.type === 'ai' }"
        >
          <div
            v-if="message.type === 'ai'"
            class="chat-ai-message-avatar"
          >
            <i class="bx bx-bot" />
          </div>
          <div
            v-else
            class="chat-ai-message-avatar"
          >
            <i class="bx bx-user" />
          </div>
          <div class="chat-ai-message-content">
            <div
              class="chat-ai-message-text"
              v-html="formatMessage(message.text)"
            />
            <div class="chat-ai-message-time">
              {{ formatTime(message.timestamp) }}
            </div>
          </div>
        </div>
        <div
          v-if="isTyping"
          class="chat-ai-message chat-ai-message-ai"
        >
          <div class="chat-ai-message-avatar">
            <i class="bx bx-bot" />
          </div>
          <div class="chat-ai-message-content">
            <div class="chat-ai-typing-indicator">
              <span />
              <span />
              <span />
            </div>
          </div>
        </div>
      </div>

      <div class="chat-ai-input">
        <textarea
          ref="inputField"
          v-model="inputMessage"
          placeholder="Digite sua mensagem..."
          class="chat-ai-input-field"
          :disabled="isTyping"
          @keydown.enter.prevent="sendMessage"
        />
        <button
          class="chat-ai-send-button"
          :disabled="!inputMessage.trim() || isTyping"
          @click="sendMessage"
        >
          <i class="bx bx-send" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import {ref, defineExpose, watch, nextTick} from 'vue';
// import http from '@/http';

// Estado do chat
const isOpen = ref(false);
const inputMessage = ref('');
const messages = ref([]);
const isTyping = ref(false);
const hasNewMessage = ref(false);
const messagesContainer = ref(null);
const inputField = ref(null);

// Toggle para abrir/fechar o chat
const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        hasNewMessage.value = false;
        nextTick(() => {
            scrollToBottom();
            inputField.value.focus();
        });

        // Se não houver mensagens, adicionar mensagem de boas-vindas
        if (messages.value.length === 0) {
            addAIMessage('Olá! Sou o assistente IA. Como posso ajudar você hoje?');
        }
    }
};

// Adicionar mensagem do usuário
const addUserMessage = (text) => {
    messages.value.push({
        type: 'user',
        text,
        timestamp: new Date()
    });
    scrollToBottom();
};

// Adicionar mensagem da IA
const addAIMessage = (text) => {
    messages.value.push({
        type: 'ai',
        text,
        timestamp: new Date()
    });

    // Se o chat estiver fechado, mostrar notificação
    if (!isOpen.value) {
        hasNewMessage.value = true;
    }

    scrollToBottom();
};

// Limpar o chat
const clearChat = () => {
    messages.value = [];
    // Adicionar mensagem de boas-vindas após limpar
    addAIMessage('Chat limpo. Como posso ajudar você agora?');
};

// Enviar mensagem
const sendMessage = async () => {
    const message = inputMessage.value.trim();
    if (!message || isTyping.value) return;

    // Adicionar mensagem do usuário
    addUserMessage(message);
    inputMessage.value = '';

    // Simular resposta da IA
    isTyping.value = true;

    try {
        // Aqui você pode integrar com sua API de IA
        // Este é um exemplo simulado, substitua pela sua implementação real
        await simulateAIResponse(message);
    } catch (error) {
        console.error('Erro ao processar mensagem:', error);
        addAIMessage('Desculpe, ocorreu um erro ao processar sua mensagem. Por favor, tente novamente.');
    } finally {
        isTyping.value = false;
    }
};

// Simulação de resposta da IA (substitua pela integração real)
const simulateAIResponse = async (message) => {
    // Aqui você pode fazer uma chamada à sua API de IA
    // Por enquanto, vamos simular com um timeout

    // Para fins de demonstração, simulamos um tempo de resposta
    await new Promise(resolve => setTimeout(resolve, 1000 + Math.random() * 2000));

    // Respostas simuladas baseadas em palavras-chave
    if (message.toLowerCase().includes('olá') || message.toLowerCase().includes('oi')) {
        addAIMessage('Olá! Como posso ajudar você hoje?');
    } else if (message.toLowerCase().includes('ajuda') || message.toLowerCase().includes('help')) {
        addAIMessage('Estou aqui para ajudar! Posso moderar conteúdo, responder perguntas ou fornecer assistência com o sistema. Do que você precisa?');
    } else if (message.toLowerCase().includes('obrigado') || message.toLowerCase().includes('obrigada')) {
        addAIMessage('De nada! Estou sempre à disposição.');
    } else {
        // Resposta padrão
        addAIMessage('Recebi sua mensagem. Como posso ajudar com isso?');
    }

    // Em um cenário real, você pode usar algo como:
    /*
    try {
      const response = await http.post('/api/v1/ai/chat', { message });
      if (response.data && response.data.message) {
        addAIMessage(response.data.message);
      }
    } catch (error) {
      console.error('Erro na API de IA:', error);
      addAIMessage('Desculpe, ocorreu um erro ao processar sua pergunta.');
    }
    */
};

// Verificar conteúdo com IA (para moderação)
// Esta função será exposta para uso em outros componentes
const verifyContent = async (content, context = {}) => {
    isTyping.value = true;

    try {
        // Simular verificação (substitua por chamada real à API)
        await new Promise(resolve => setTimeout(resolve, 800 + Math.random() * 1200));

        // Simulação de análise baseada em palavras-chave simples
        const lowercaseContent = content.toLowerCase();
        let result = {
            approved: true,
            message: '',
            suggestions: []
        };

        // Verificações simples baseadas em palavras-chave
        if (lowercaseContent.includes('palavrão') ||
            lowercaseContent.includes('xingamento') ||
            lowercaseContent.includes('ofensa')) {
            result.approved = false;
            result.message = 'O conteúdo contém termos inapropriados.';
            result.suggestions.push('Remova termos ofensivos ou inadequados.');
        }

        // Verificar comprimento (apenas para demonstração)
        if (lowercaseContent.length < 3) {
            result.approved = false;
            result.message = 'O conteúdo é muito curto.';
            result.suggestions.push('Utilize um termo mais descritivo.');
        }

        if (context.type === 'category' && !result.approved) {
            // Adicionar mensagem no chat sobre moderação
            const message = `⚠️ Moderação: "${content}" não foi aprovado como uma nova categoria.\nMotivo: ${result.message}\nSugestão: ${result.suggestions.join(' ')}`;
            addAIMessage(message);

            // Garantir que o chat seja aberto para mostrar a notificação
            if (!isOpen.value) {
                toggleChat();
            }
        }

        return result;

        // Em um cenário real:
        /*
        const response = await http.post('/api/v1/ai/moderate', {
          content,
          context
        });
        return response.data;
        */
    } catch (error) {
        console.error('Erro ao verificar conteúdo:', error);

        // Adicionar mensagem no chat sobre o erro
        addAIMessage('⚠️ Ocorreu um erro ao verificar o conteúdo. Por favor, tente novamente ou entre em contato com o suporte.');

        return {
            approved: false,
            message: 'Erro ao verificar conteúdo.',
            suggestions: ['Tente novamente mais tarde.']
        };
    } finally {
        isTyping.value = false;
    }
};

// Formatar a mensagem (suporte a markdown básico)
const formatMessage = (text) => {
    // Substituir URLs por links clicáveis
    let formattedText = text.replace(
        /(https?:\/\/[^\s]+)/g,
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
    );

    // Substituir **texto** por negrito
    formattedText = formattedText.replace(
        /\*\*(.*?)\*\*/g,
        '<strong>$1</strong>'
    );

    // Substituir _texto_ por itálico
    formattedText = formattedText.replace(
        /_(.*?)_/g,
        '<em>$1</em>'
    );

    // Substituir quebras de linha por <br>
    formattedText = formattedText.replace(/\n/g, '<br>');

    return formattedText;
};

// Formatar timestamp para horário legível
const formatTime = (timestamp) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
};

// Rolar para o final das mensagens
const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

// Observar mudanças nas mensagens para rolar automaticamente
watch(() => messages.value.length, () => {
    scrollToBottom();
});

// Ajustar altura do textarea conforme o conteúdo
watch(inputMessage, () => {
    if (inputField.value) {
        inputField.value.style.height = 'auto';
        inputField.value.style.height = `${inputField.value.scrollHeight}px`;
    }
});

// Expor métodos e propriedades para uso global
defineExpose({
    addUserMessage,
    addAIMessage,
    verifyContent,
    openChat: () => {
        isOpen.value = true;
        nextTick(() => {
            scrollToBottom();
            inputField.value.focus();
        });
    },
    closeChat: () => {
        isOpen.value = false;
    }
});
</script>

<style scoped>
.chat-ai-wrapper {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: var(--bs-body-font-family, 'Poppins', sans-serif);
}

.chat-ai-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: var(--bs-primary, #4361ee);
    color: white;
    border: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.3s ease;
}

.chat-ai-toggle:hover {
    transform: scale(1.05);
    background-color: var(--bs-primary-dark, #3a56d6);
}

.chat-ai-toggle i {
    font-size: 28px;
}

.chat-ai-toggle.pulse {
    animation: pulse 2s infinite;
}

.badge-notification {
    position: absolute;
    top: 0;
    right: 0;
    width: 12px;
    height: 12px;
    background-color: #ff4757;
    border-radius: 50%;
    border: 2px solid white;
}

.chat-ai-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 350px;
    height: 500px;
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s ease;
}

.chat-ai-header {
    padding: 15px;
    background-color: var(--bs-primary, #4361ee);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-ai-title {
    display: flex;
    align-items: center;
    font-weight: 600;
}

.chat-ai-title i {
    margin-right: 8px;
    font-size: 18px;
}

.chat-ai-actions {
    display: flex;
}

.chat-ai-action {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.chat-ai-action:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.chat-ai-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.chat-ai-message {
    display: flex;
    gap: 10px;
    max-width: 85%;
}

.chat-ai-message-user {
    margin-left: auto;
    flex-direction: row-reverse;
}

.chat-ai-message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #f1f1f1;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.chat-ai-message-user .chat-ai-message-avatar {
    background-color: var(--bs-primary-light, #d1d9ff);
    color: var(--bs-primary, #4361ee);
}

.chat-ai-message-ai .chat-ai-message-avatar {
    background-color: var(--bs-primary, #4361ee);
    color: white;
}

.chat-ai-message-content {
    background-color: #f1f1f1;
    padding: 10px 12px;
    border-radius: 12px;
    position: relative;
    word-break: break-word;
}

.chat-ai-message-user .chat-ai-message-content {
    background-color: var(--bs-primary-light, #d1d9ff);
}

.chat-ai-message-ai .chat-ai-message-content {
    background-color: #f1f1f1;
}

.chat-ai-message-text {
    font-size: 14px;
    line-height: 1.4;
}

.chat-ai-message-time {
    font-size: 10px;
    color: #888;
    margin-top: 4px;
    text-align: right;
}

.chat-ai-input {
    padding: 12px;
    border-top: 1px solid #eaeaea;
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

.chat-ai-input-field {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 18px;
    padding: 10px 15px;
    resize: none;
    max-height: 100px;
    min-height: 38px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
    line-height: 1.4;
}

.chat-ai-input-field:focus {
    border-color: var(--bs-primary, #4361ee);
}

.chat-ai-send-button {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background-color: var(--bs-primary, #4361ee);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
}

.chat-ai-send-button:hover:not(:disabled) {
    background-color: var(--bs-primary-dark, #3a56d6);
}

.chat-ai-send-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.chat-ai-typing-indicator {
    display: flex;
    align-items: center;
    column-gap: 5px;
    padding: 5px 0;
}

.chat-ai-typing-indicator span {
    height: 8px;
    width: 8px;
    background-color: #aaa;
    border-radius: 50%;
    display: block;
    opacity: 0.4;
}

.chat-ai-typing-indicator span:nth-child(1) {
    animation: typingAnimation 1s infinite 0s;
}

.chat-ai-typing-indicator span:nth-child(2) {
    animation: typingAnimation 1s infinite 0.2s;
}

.chat-ai-typing-indicator span:nth-child(3) {
    animation: typingAnimation 1s infinite 0.4s;
}

@keyframes typingAnimation {
    0% {
        opacity: 0.4;
        transform: translateY(0);
    }
    50% {
        opacity: 1;
        transform: translateY(-5px);
    }
    100% {
        opacity: 0.4;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(67, 97, 238, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(67, 97, 238, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(67, 97, 238, 0);
    }
}

/* Responsividade para dispositivos móveis */
@media (max-width: 576px) {
    .chat-ai-window {
        width: calc(100vw - 40px);
        height: 60vh;
        right: 0;
        bottom: 80px;
    }
}
</style>