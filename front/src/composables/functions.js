import router from '@/router';
import Swal from "sweetalert2";
import {notifyError} from "@/composables/messages";

export function getUrl(session) {
    const values = JSON.parse(localStorage.getItem(session));
    let url = values.url + '/?';
    const params = values.params;
    const keys = Object.keys(values.params);

    for (let i = 0; i < keys.length; i++) {

        let result = params[keys[i]] !== 'null' && params[keys[i]] != null ? params[keys[i]] : '';
        url += `&${keys[i]}=${result}`;
    }

    return url;
}

export function Forbidden(response) {
    notifyError(response.response.data);
}

export async function showAlertConfirm(message) {
    const text = message || "Seus dados serão removidos e não poderão mais ser recuperados.";

    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-soft-success",
            cancelButton: "btn btn-soft-danger me-3"
        },
        buttonsStyling: false
    });

    return swalWithBootstrapButtons.fire({
        title: "Você tem certeza?",
        text: text,
        icon: "warning",
        showCancelButton: true,
        cancelButtonColor: "#f46a6a",
        confirmButtonColor: "#34c38f",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Confirmar",
        reverseButtons: true
    }).then((result) => {
        return result.isConfirmed;
    });
}

export function loadTable() {
    const tbody = document.querySelector('#table tbody');
    if (tbody) tbody.style.opacity = '.2'
    loadCard();
}

export function loadCard(id) {
    if (id) {
        const element = document.getElementById(id);
        element.style.opacity = '.2'
    }
    const placeholderElement = document.querySelector('.simplebar-placeholder');
    const width = parseInt(placeholderElement.offsetWidth) / 2 + 30;

    if (document.getElementById('spinner')) {
        document.getElementById('spinner').style.display = 'block'
        document.getElementById('spinner').style.right = `calc(50% - ${width}px)`
    }
}

export function endLoadTable() {
    const tbody = document.querySelector('#table tbody');
    if (tbody) tbody.style.opacity = '1'

    endLoadCard();
}

export function endLoadCard(id) {
    if (id) {
        const element = document.getElementById(id);
        element.style.opacity = '1'
    }
    document.getElementById('spinner').style.display = 'none'
    document.getElementById('spinner').style.right = `calc(50% - 2.5rem)`
}

// Novas funções para uso com RegionalSpinner
export function loadTableWithRegionalSpinner(spinnerId = 'table-spinner', message = 'Carregando dados...') {
    const tbody = document.querySelector('#table tbody');
    if (tbody) tbody.style.opacity = '.6';
    
    // Importa o composable dinamicamente
    import('@/composables/useRegionalSpinner').then(({ useRegionalSpinner }) => {
        const { showTableSpinner } = useRegionalSpinner();
        showTableSpinner(spinnerId, message);
    });
}

export function endLoadTableWithRegionalSpinner(spinnerId = 'table-spinner') {
    const tbody = document.querySelector('#table tbody');
    if (tbody) tbody.style.opacity = '1';
    
    // Importa o composable dinamicamente
    import('@/composables/useRegionalSpinner').then(({ useRegionalSpinner }) => {
        const { hideSpinner } = useRegionalSpinner();
        hideSpinner(spinnerId);
    });
}

export function loadPaginationWithRegionalSpinner(spinnerId = 'pagination-spinner', message = 'Carregando página...') {
    const tbody = document.querySelector('#table tbody');
    if (tbody) tbody.style.opacity = '.6';
    
    // Importa o composable dinamicamente
    import('@/composables/useRegionalSpinner').then(({ useRegionalSpinner }) => {
        const { showPaginationSpinner } = useRegionalSpinner();
        showPaginationSpinner(spinnerId, message);
    });
}

export function endLoadPaginationWithRegionalSpinner(spinnerId = 'pagination-spinner') {
    const tbody = document.querySelector('#table tbody');
    if (tbody) tbody.style.opacity = '1';
    
    // Importa o composable dinamicamente
    import('@/composables/useRegionalSpinner').then(({ useRegionalSpinner }) => {
        const { hideSpinner } = useRegionalSpinner();
        hideSpinner(spinnerId);
    });
}

export function convertDateText(dateTime) {
    if(!dateTime) return '-'
    let date = dateTime.split(' ');
    const months = [
        'Jan', 'Fev', 'Mar', 'Abr',
        'Mai', 'Jun', 'Jul', 'Ago',
        'Set', 'Out', 'Nov', 'Dez',
    ];

    date = date[0].split('-');
    const month = months[parseInt(date[1]) - 1];

    return `${date[2]} ${month}, ${date[0]}`;
}

export function convertDateIso(date) {
    if (!date) return '';

    const values = date.split('/');

    return `${values[2]}-${values[1]}-${values[0]}`;
}

export function convertHour(dateTime) {
    let date = dateTime.split(' ');
    date = date[1].split('.');

    date = date[0].split(':');

    return `${date[0]}:${date[1]}`;
}

export function generateNickname(fullName) {
    // Dividir o nome completo em partes
    let nameParts = fullName.trim().split(" ");

    // Obter a primeira letra do primeiro nome
    let firstNameInitial = nameParts[0].charAt(0);

    // Obter a primeira letra do último nome
    let lastNameInitial = nameParts[nameParts.length - 1].charAt(0);

    // Combinar as letras para formar o nickname
    let nickname = firstNameInitial + lastNameInitial;

    return nickname.toUpperCase(); // Opcional: converter para maiúsculas
}

export function addZeros(string) {
    return String(string).padStart(2, '0');
}

export function httpError(response) {
    if (response.status === 401) {
        notifyError('Você precisa se autenticar!');
        router.push('/login');
    }

    if (typeof response.data === 'string') {
        notifyError(response.data);
    }
}

export function encodeId(id) {
    return btoa(id.toString());
}

export function timestampToHour(timestamp) {
    const date = new Date(timestamp * 1000);

    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${hours}:${minutes}`;
}

export function generateRandomColors(count) {
    if (typeof count !== 'number' || count <= 0 || !Number.isInteger(count)) {
        throw new Error("O parâmetro deve ser um número inteiro positivo.");
    }

    const colors = [];
    const step = 360 / count; // Dividir igualmente no espectro de cores

    for (let i = 0; i < count; i++) {
        const hue = i * step; // Distribuir os tons uniformemente
        const saturation = 70; // Saturação fixa (70%)
        const value = 90; // Brilho fixo (90%)
        colors.push(hsvToHex(hue, saturation, value));
    }

    return colors;
}

// Função para converter HSV para HEX
function hsvToHex(h, s, v) {
    s /= 100;
    v /= 100;
    const c = v * s;
    const x = c * (1 - Math.abs((h / 60) % 2 - 1));
    const m = v - c;
    let r = 0, g = 0, b = 0;

    if (h >= 0 && h < 60) {
        r = c;
        g = x;
        b = 0;
    } else if (h >= 60 && h < 120) {
        r = x;
        g = c;
        b = 0;
    } else if (h >= 120 && h < 180) {
        r = 0;
        g = c;
        b = x;
    } else if (h >= 180 && h < 240) {
        r = 0;
        g = x;
        b = c;
    } else if (h >= 240 && h < 300) {
        r = x;
        g = 0;
        b = c;
    } else if (h >= 300 && h < 360) {
        r = c;
        g = 0;
        b = x;
    }

    r = Math.round((r + m) * 255);
    g = Math.round((g + m) * 255);
    b = Math.round((b + m) * 255);

    return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase()}`;
}