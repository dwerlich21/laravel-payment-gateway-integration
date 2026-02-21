import {createWebHistory, createRouter} from "vue-router";
import routes from './routes';
import env from "@/env";
import {useAuthStore} from "@/stores/auth.js";

const router = createRouter({
    history: createWebHistory(),
    routes,
    mode: 'history',
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return {top: 0, left: 0};
        }
    },
});

router.beforeEach(async (routeTo, routeFrom, next) => {

    const authRequired = routeTo.matched.some((route) => route.meta.authRequired);
    // const requiredPermission = routeTo.meta.permission;

    if (!authRequired) {
        return next();
    }

    const authStore = useAuthStore();

    // Check if user is already logged in
    if (authStore.loggedIn) {
        return next();
    }

    // Try to refresh user data from cookies

    // After refresh, check if user is now logged in
    if (!authStore.loggedIn) {
        await authStore.refresh();
    } else {
        return next({name: 'login'});
    }

    return next();
    // let permissions = localStorage.getItem('permissions');
    // permissions =  permissions ? JSON.parse(permissions) : [];
    //
    // if (requiredPermission && permissions.indexOf(requiredPermission) < 0) {
    //     notifyError('Não autorizado');
    //     next({ name: 'dashboard' });
    // }
});

router.beforeResolve(async (routeTo, routeFrom, next) => {
    try {
        for (const route of routeTo.matched) {
            await new Promise((resolve, reject) => {
                if (route.meta && route.meta.beforeResolve) {
                    route.meta.beforeResolve(routeTo, routeFrom, (...args) => {
                        if (args.length) {
                            next(...args);
                            reject(new Error('Redirected'));
                        } else {
                            resolve();
                        }
                    });
                } else {
                    resolve();
                }
            });
        }
    } catch (error) {
        return;
    }
    document.title = routeTo.meta.title + ' | ' + env.title;
    next();
});

export default router;
