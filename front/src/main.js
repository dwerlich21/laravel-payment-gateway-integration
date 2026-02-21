import './utils/passiveEvents'; // DEVE ser a primeira importação - patch para event listeners passivos
import './utils/suppressWarnings'; // Suprimir warnings específicos

import {createApp} from 'vue';
import App from './App.vue';
import router from "./router";
import AOS from 'aos';
import 'aos/dist/aos.css';
import { createPinia } from 'pinia';
import { permission } from './directives/permission';

import BootstrapVue3 from 'bootstrap-vue-3';
import { vBTooltip } from 'bootstrap-vue-3';
import vClickOutside from "click-outside-vue3";
import VueApexCharts from "vue3-apexcharts";
import Maska from 'maska';

import '@/assets/scss/config/corporate/app.scss';
import "@/assets/scss/app.scss"
import '@/assets/scss/colors.scss';
import '@vueform/slider/themes/default.css';
import '@/assets/scss/mermaid.min.css';

import {createNotivue} from 'notivue'
import '@/assets/scss/notification.css'

// service de websocket para chat
// import '@/services/echo';


const notivue = createNotivue({
    position: 'top-right',
    limit: 4,
    enqueue: true,
    pauseOnHover: false,
    avoidDuplicates: true,
})

const pinia = createPinia()

AOS.init({
    easing: 'ease-out-back',
    duration: 200
});

// Suprimir warnings específicos do Vue
const app = createApp(App);

// Configurar handler de warnings personalizado
app.config.warnHandler = (msg, instance, trace) => {
    // Ignorar warnings específicos
    if (msg.includes('Failed to resolve directive: b-tooltip')) return;
    if (msg.includes('expose() should be passed a plain object')) return;
    
    // Para outros warnings, você pode escolher logar ou não
    // console.warn(msg, instance, trace);
};

// Desativar warnings de performance do Vue (opcional)
app.config.performance = false;

// Configurar mensagem de erro personalizada (opcional)
app.config.errorHandler = (err, instance, info) => {
    // Apenas logar erros críticos
    if (!err.message?.includes('passive event listener')) {
        console.error('Vue Error:', err, info);
    }
};

app
    .directive('permission', permission)
    .directive('b-tooltip', vBTooltip)
    .use(notivue)
    .use(pinia)
    .use(router)
    .use(VueApexCharts)
    .use(BootstrapVue3)
    .use(Maska)
    .use(vClickOutside).mount('#app');
