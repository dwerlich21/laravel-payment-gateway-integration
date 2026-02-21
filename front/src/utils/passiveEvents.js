// Patch para adicionar suporte a event listeners passivos e suprimir violations
// Este código deve ser executado antes de qualquer biblioteca que adicione event listeners

(function() {
    // Salvar referência original do addEventListener
    const originalAddEventListener = EventTarget.prototype.addEventListener;
    
    // Override addEventListener para adicionar passive por padrão em eventos de scroll
    EventTarget.prototype.addEventListener = function(type, listener, options) {
        let modifiedOptions = options;
        
        // Lista de eventos que devem ser passivos por padrão
        const passiveEvents = ['wheel', 'mousewheel', 'touchstart', 'touchmove', 'scroll'];
        
        // Se for um evento de scroll/touch e não tem opções definidas
        if (passiveEvents.includes(type)) {
            if (typeof options === 'boolean') {
                modifiedOptions = {
                    capture: options,
                    passive: true
                };
            } else if (!options) {
                modifiedOptions = { passive: true };
            } else if (typeof options === 'object' && !('passive' in options)) {
                modifiedOptions = {
                    ...options,
                    passive: true
                };
            }
        }
        
        // Chamar o addEventListener original com as opções modificadas
        return originalAddEventListener.call(this, type, listener, modifiedOptions);
    };
    
    // Suprimir violations no console
    const originalConsoleError = console.error;
    const originalConsoleWarn = console.warn;
    
    // Filtrar mensagens de violation
    const filterViolations = (method) => {
        return function(...args) {
            const message = args[0]?.toString() || '';
            
            // Ignorar violations relacionadas a passive event listeners
            if (message.includes('[Violation]') && 
                (message.includes('non-passive event listener') || 
                 message.includes('passive event listener'))) {
                return;
            }
            
            // Chamar o método original para outras mensagens
            method.apply(console, args);
        };
    };
    
    console.error = filterViolations(originalConsoleError);
    console.warn = filterViolations(originalConsoleWarn);
    
    // Também interceptar console.log se violations aparecerem lá
    const originalConsoleLog = console.log;
    console.log = function(...args) {
        const message = args[0]?.toString() || '';
        if (message.includes('[Violation]')) return;
        originalConsoleLog.apply(console, args);
    };
})();

// Adicionar suporte para navegadores antigos
(function() {
    let supportsPassive = false;
    try {
        const opts = Object.defineProperty({}, 'passive', {
            get: function() {
                supportsPassive = true;
                return true;
            }
        });
        window.addEventListener('testPassive', null, opts);
        window.removeEventListener('testPassive', null, opts);
    } catch (e) {
        supportsPassive = false;
    }
    
    // Exportar flag para uso em outros módulos se necessário
    window.supportsPassiveEvents = supportsPassive;
})();

export default {};