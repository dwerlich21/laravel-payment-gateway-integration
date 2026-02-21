export default [
    {
        path: "/login",
        name: "login",
        component: () => import("@/views/account/Login.vue"),
        meta: {
            title: "Login",
        },
    },

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
            title: "Produtos",
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
        redirect: {name: "dashboard"},
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
