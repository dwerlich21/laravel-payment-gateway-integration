import http from '@/http';
import {notifyError, notifySuccess} from "@/composables/messages";
import {useGlobalSpinner} from '@/composables/useGlobalSpinner';

export default class OrderService {
    constructor() {
        this.spinner = useGlobalSpinner();
    }

    async getCheckoutConfig() {
        try {
            const response = await http.get('checkout');
            return response.data.data || {};
        } catch (error) {
            notifyError(error.response?.data || 'Erro ao carregar configuração de checkout');
            throw error;
        }
    }

    async confirmPayment(orderId, customerData = {}) {
        try {
            const response = await http.put(`checkout/${orderId}/confirm`, customerData);
            return response.data;
        } catch (error) {
            console.error('Erro ao confirmar pagamento:', error);
            throw error;
        }
    }

    async checkout(data) {
        try {
            this.spinner.showDataSpinner({message: 'Processando pedido...', zIndex: 99});
            const response = await http.post('checkout', data);
            return response.data;
        } catch (error) {
            notifyError(error.response?.data || 'Erro ao criar pedido');
            throw error;
        } finally {
            setTimeout(() => this.spinner.hideSpinner(), 350);
        }
    }

    async getAll() {
        try {
            const response = await http.get('orders');
            return response.data.data?.data || response.data.data || [];
        } catch (error) {
            notifyError(error.response?.data || 'Erro ao carregar pedidos');
            throw error;
        } finally {
            setTimeout(() => this.spinner.hideSpinner(), 350);
        }
    }
}
