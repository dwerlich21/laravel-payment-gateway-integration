export default [
    {
        path: "/login",
        name: "login",
        component: () => import("@/views/account/Login.vue"),
        meta: {
            title: "Login",
            // beforeResolve(routeTo, routeFrom, next) {
            //     if (store.getters["auth/loggedIn"]) {
            //         next({name: "dashboard"});
            //     } else {
            //         next();
            //     }
            // },
        },
    },

    // {
    //     path: "/credenciais",
    //     name: "credentials",
    //     component: () => import("@/views/account/credentials.vue"),
    //     meta: {
    //         title: "Verificando Credenciais",
    //     },
    // },

    {
        path: "/esqueceu-senha",
        name: "forgot-password",
        component: () => import("@/views/account/ForgotPassword.vue"),
        meta: {
            title: "Recuperar Senha",
        },
    },
    {
        path: "/recuperar-senha",
        name: "change-password",
        component: () => import("@/views/account/ChangePassword.vue"),
        meta: {
            title: "Resetar Senha",
        },
    },
    {
        path: "/logout",
        name: "logout",
        component: () => import("@/views/account/Logout.vue"),
        meta: {
            title: "Logout",
        },
    },
    {
        path: "/",
        name: "dashboard",
        meta: {
            title: "Dashboard",
        },
        component: () => import("@/views/dashboard/Index.vue"),
    },

    {
        path: "/meu-perfil",
        name: "my-profile",
        meta: {
            title: "Meu Perfil",
            authRequired: true,
        },
        component: () => import("@/views/profile/MyProfile.vue"),
    },

    // Produtos & Checkout
    {
        path: "/produtos",
        name: "products",
        meta: {
            title: "Produtos",
        },
        component: () => import("@/views/products/Products.vue"),
    },
    {
        path: "/checkout/:id",
        name: "checkout",
        meta: {
            title: "Checkout",
            authRequired: true,
        },
        component: () => import("@/views/products/Checkout.vue"),
    },

    // Notificações
    {
        path: "/notificacoes",
        name: "notifications",
        meta: {
            title: "Notificações",
            authRequired: true,
        },
        component: () => import("@/views/notifications/Index.vue"),
    },
];
