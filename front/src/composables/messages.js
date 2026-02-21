import {push} from 'notivue';

export function notifyError(data) {
    if (typeof data === 'string') {
        push.error({
            title: data,
            duration: 5000
        });
        return;
    }

    let title = data.message;
    let message = '';

    if (data.errors && Object.keys(data.errors).length > 0) {
        message += '\n';
        const erros = Array.from(new Set(Object.values(data.errors)));
        erros.forEach(erro => {
            erro.forEach(er => {
                message += `• ${er.trim()}\n`;
            });
        });
    }
    push.error({
        title,
        message,
        duration: 5000
    })
}

export function notifySuccess(message) {
    push.success({
        title: message,
        duration: 5000
    })
}

export function notifyInfo(message) {
    push.info({
        title: message,
        duration: 5000
    })
}

export function getMessageError(data) {
    const keys = Object.keys(data);
    notifyError(data[keys[0]][0]);
}
