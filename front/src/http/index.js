import axios from "axios";
import env from "@/env";
import router from "@/router";
import {notifyError} from "@/composables/messages";

const http = axios.create({
    baseURL: env.api,
    withCredentials: true, // Enable cookies
});

// Adicionando um interceptador de requisição ao Axios
http.interceptors.request.use(
    (config) => {
        // Cookies are sent automatically with withCredentials: true
        // No need to manually add Authorization header
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Adicionando um interceptador de resposta para lidar com erros
http.interceptors.response.use(
    (response) => {
        // Cookies are handled automatically by the browser
        return response;
    },
    (error) => {
        if (error.response && error.response.status === 401) {
            notifyError('Não autorizado!');
            // No need to remove token from localStorage as we use cookies now
            if (router.name !== 'login') router.push('/login'); // Redireciona o usuário para a página de login
        } else if (error.response && error.response.status === 403) {
            notifyError('Acesso negado!');
            if (router.name !== 'dashboard') router.push('/');
        } else {
            return Promise.reject(error);
        }
    }
);

export default http;
