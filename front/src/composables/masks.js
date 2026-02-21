export function maskPhone(phone) {
    // Remove todos os caracteres que não são dígitos
    const cleanedText = phone.replace(/\D/g, "");

    // Aplica a máscara de telefone progressivamente
    let formattedPhone = cleanedText;

    if (cleanedText.length > 2) {
        formattedPhone = '(' + cleanedText.slice(0, 2) + ') ' + cleanedText.slice(2);
    }
    if (cleanedText.length > 6) {
        formattedPhone = '(' + cleanedText.slice(0, 2) + ') ' + cleanedText.slice(2, 6) + '-' + cleanedText.slice(6, 10);
    }
    if (cleanedText.length > 10) {
        formattedPhone = '(' + cleanedText.slice(0, 2) + ') ' + cleanedText.slice(2, 7) + '-' + cleanedText.slice(7, 11);
    }

    return formattedPhone;
}

export function maskDate(date) {
    // Remove todos os caracteres que não são dígitos
    const cleanedText = date.replace(/\D/g, "");

    // Aplica a máscara de data progressivamente
    let formattedDate = cleanedText;

    if (cleanedText.length > 2) {
        formattedDate = cleanedText.slice(0, 2) + '/' + cleanedText.slice(2);
    }
    if (cleanedText.length > 4) {
        formattedDate = cleanedText.slice(0, 2) + '/' + cleanedText.slice(2, 4) + '/' + cleanedText.slice(4, 8);
    }

    return formattedDate;
}

export function maskCpfCnpj(cpfCnpj) {
    // Remove todos os caracteres que não são dígitos
    const cleanedText = cpfCnpj.replace(/\D/g, "");

    // Aplica a máscara de CPF ou CNPJ com base no comprimento
    let formattedText = cleanedText;

    if (cleanedText.length <= 11) {
        // Máscara de CPF: ###.###.###-##
        if (cleanedText.length > 3) {
            formattedText = cleanedText.slice(0, 3) + '.' + cleanedText.slice(3);
        }
        if (cleanedText.length > 6) {
            formattedText = formattedText.slice(0, 7) + '.' + cleanedText.slice(6);
        }
        if (cleanedText.length > 9) {
            formattedText = formattedText.slice(0, 11) + '-' + cleanedText.slice(9);
        }
    } else {
        // Máscara de CNPJ: ##.###.###/####-##
        if (cleanedText.length > 2) {
            formattedText = cleanedText.slice(0, 2) + '.' + cleanedText.slice(2);
        }
        if (cleanedText.length > 5) {
            formattedText = formattedText.slice(0, 6) + '.' + cleanedText.slice(5);
        }
        if (cleanedText.length > 8) {
            formattedText = formattedText.slice(0, 10) + '/' + cleanedText.slice(8);
        }
        if (cleanedText.length > 12) {
            formattedText = formattedText.slice(0, 15) + '-' + cleanedText.slice(12, 14);
        }
    }

    return formattedText;
}