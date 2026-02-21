import http from '@/http';
import {notifyError} from "@/composables/messages";
import {useGlobalSpinner} from '@/composables/useGlobalSpinner';

export default class ProductService {
    constructor() {
        this.spinner = useGlobalSpinner();
    }

    async getAll() {
        try {
            const response = await http.get('products');
            return response.data.data?.data || response.data.data || [];
        } catch (error) {
            notifyError(error.response?.data || 'Erro ao carregar produtos');
            throw error;
        } finally {
            setTimeout(() => this.spinner.hideSpinner(), 350);
        }
    }
}
