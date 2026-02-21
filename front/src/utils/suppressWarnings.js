// Suprimir warnings específicos do console
const originalWarn = console.warn;
const originalError = console.error;
const originalLog = console.log;

// Override console.warn
console.warn = (...args) => {
    const message = args[0]?.toString() || '';
    
    // Ignorar warnings específicos
    if (message.includes('[Violation]')) return;
    if (message.includes('Added non-passive event listener')) return;
    if (message.includes('passive event listener')) return;
    if (message.includes('[Vue warn]')) {
        if (message.includes('Failed to resolve directive: b-tooltip')) return;
        if (message.includes('expose() should be passed a plain object')) return;
    }
    
    // Para outros warnings, chamar o método original
    originalWarn.apply(console, args);
};

// Override console.log para filtrar violations
console.log = (...args) => {
    const message = args[0]?.toString() || '';
    
    // Ignorar violations (incluindo forced reflow)
    if (message.includes('[Violation]')) return;
    if (message.includes('Forced reflow')) return;
    
    // Para outros logs, chamar o método original
    originalLog.apply(console, args);
};

// Override console.error para filtrar erros específicos (opcional)
console.error = (...args) => {
    const message = args[0]?.toString() || '';
    
    // Ignorar erros específicos se necessário
    if (message.includes('passive event listener')) return;
    
    // Para outros erros, chamar o método original
    originalError.apply(console, args);
};

export default {};