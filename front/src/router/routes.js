// import store from "@/state/store";

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
            authRequired: true,
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
    {
        path: "/usuarios",
        name: "users",
        meta: {
            title: "Usuários",
            authRequired: true,
            permission: 'users.index'
        },
        component: () => import("@/views/users/Users.vue"),
    },

    {
        path: "/usuarios/cadastrar/:id?",
        name: "users-form",
        meta: {
            title: "Cadastro de Usuários",
            authRequired: true,
            permission: 'contacts.create'
        },
        component: () => import("@/views/users/Form.vue"),
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

    // Audit Logs
    {
        path: "/audit-logs",
        name: "audit-logs",
        meta: {
            title: "Logs de Auditoria",
            authRequired: true,
        },
        component: () => import("@/views/audit/Index.vue"),
    },
];
