import {defineStore} from 'pinia'
import http from "@/http/index.js";
import env from "@/env.js";
import router from "@/router/index.js";
import {notifyError} from "@/composables/messages.js";
import {usePermissions} from "@/utils/permissions.js";

export const useAuthStore = defineStore('auth', {
    state: () => ({
        currentUser: null,
        enums: [],
        isRefreshing: false,
    }),

    getters: {
        loggedIn: (state) => !!(state.currentUser && Object.keys(state.currentUser).length > 0),

        getUser: (state) => state.currentUser,
    },

    actions: {
        isRefreshing: false,
        setCurrentUser(newValue) {
            this.currentUser = newValue
        },

        setEnums(newValue) {
            this.enums = newValue
        },

        setRefreshing(value) {
            this.isRefreshing = value
        },

        async logout() {
            try {
                await http.post(env.api + 'logout')
            } catch (error) {
                // Continue with logout even if request fails
            }
            this.setCurrentUser(null)
            usePermissions.clearPermissions()
        },

        initialize() {
            // Try to get user info from API
            // Cookies will be sent automatically if they exist
            http.get(env.api + 'me')
                .then(response => {
                    this.setCurrentUser(response.data.user);
                    usePermissions.setPermissions(response.data.permissions || []);
                    router.push('/');
                })
                .catch(() => {
                    // User is not authenticated
                    this.setCurrentUser({});
                    usePermissions.clearPermissions();
                });
        },

        refresh() {
            this.setRefreshing(true)

            // Cookies will be sent automatically
            http.get(env.api + 'me')
                .then(response => {
                    this.setCurrentUser(response?.data?.user);
                    usePermissions.setPermissions(response?.data?.permissions || []);
                })
                .catch(() => {
                    this.setCurrentUser(null);
                    usePermissions.clearPermissions();
                    router.push('/login');
                    notifyError('Você precisa se autenticar novamente.');
                })
                .finally(() => {
                    this.setRefreshing(false);
                })
        },

        getEnums() {
            http.get(env.api + 'enums')
                .then(response => {
                    this.setEnums(response.data.data);
                })
                .catch(error => {
                    console.error(error);
                });
        },
    }
})