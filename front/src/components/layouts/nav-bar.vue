<script setup>
import {computed, onMounted} from 'vue';
import {useLayoutStore} from "@/stores/layout.js";
import {useAuthStore} from "@/stores/auth.js";
import http from "@/http";
import {notifyError, notifySuccess} from "@/composables/messages";
import {useRouter} from 'vue-router';
import env from "@/env";
import NotificationBell from './NotificationBell.vue';

// Stores
const layoutStore = useLayoutStore();
const authStore = useAuthStore();
const router = useRouter();

// Data structures available for future use

// Computed
const user = computed(() => {
    return authStore.currentUser;
});
// Methods
const toggleHamburgerMenu = () => {
    var windowSize = document.documentElement.clientWidth;
    let layoutType = document.documentElement.getAttribute("data-layout");
    let visiblilityType = document.documentElement.getAttribute("data-sidebar-visibility");

    document.documentElement.setAttribute("data-sidebar-visibility", "show");

    //For collapse vertical menu
    if (visiblilityType === "show" && (layoutType === "vertical" || layoutType === "semibox")) {
        if (windowSize < 1025 && windowSize > 767) {
            document.body.classList.remove("vertical-sidebar-enable");
            if (document.documentElement.getAttribute("data-sidebar-size") == "sm") {
                layoutStore.changeSidebarType('');
                document.documentElement.setAttribute("data-sidebar-size", "");
            } else {
                document.documentElement.setAttribute("data-sidebar-size", "sm");
                layoutStore.changeSidebarType('sm');
            }
        } else if (windowSize > 1025) {
            document.body.classList.remove("vertical-sidebar-enable");
            if (document.documentElement.getAttribute("data-sidebar-size") == "lg") {
                layoutStore.changeSidebarType('sm');
                document.documentElement.setAttribute("data-sidebar-size", "sm");
            } else {
                document.documentElement.setAttribute("data-sidebar-size", "lg");
                layoutStore.changeSidebarType('lg');
            }
        } else if (windowSize <= 767) {
            document.body.classList.add("vertical-sidebar-enable");
            document.documentElement.setAttribute("data-sidebar-size", "lg");
        }
    }

    if (windowSize > 767 && layoutStore.sidebarSize === "sm") {
        document.querySelector(".hamburger-icon").classList.toggle("open");
    } else {
        document.querySelector(".hamburger-icon").classList.remove("open");
    }
};

const toggleDarkMode = () => {
    const currentMode = layoutStore.mode;
    const newMode = currentMode === 'dark' ? 'light' : 'dark';

    // Usa a store para gerenciar o tema
    layoutStore.changeMode({mode: newMode});
};

const verifyCnpj = () => {
    const cnpj = document.getElementById('search-cnpj').value;

    http.post('leads/verificar-cnpj', {cnpj})
        .then((response) => {
            if (response.data.message > 0) {
                notifyError('CNPJ já cadastrado');
                router.push('/leads/' + response.data.message);
            } else {
                if (document.getElementById('form')) {
                    document.getElementById('form').reset();
                    document.getElementById('cnpj').value = cnpj;
                }
                notifySuccess('CNPJ sem cadastro');
                router.push('/leads/cadastrar?cnpj=' + cnpj);
            }
        })
        .catch((e) => {
            console.error('verificar-cnpj: ', e);
        })
};

// Note: updateNotification method available if needed for future notifications feature

// Lifecycle
onMounted(() => {
    var currentWindowSize = document.documentElement.clientWidth;
    if (currentWindowSize > 767 && layoutStore.sidebarSize === "sm") {
        if (document.querySelector(".hamburger-icon")) document.querySelector(".hamburger-icon").classList.toggle("open");
    } else {
        if (document.querySelector(".hamburger-icon")) document.querySelector(".hamburger-icon").classList.remove("open");
    }

    document.addEventListener("scroll", function () {
        var pageTopbar = document.getElementById("page-topbar");
        if (pageTopbar) {
            document.body.scrollTop >= 50 || document.documentElement.scrollTop >= 50 ? pageTopbar.classList.add(
                "topbar-shadow") : pageTopbar.classList.remove("topbar-shadow");
        }
    });
    if (document.getElementById("topnav-hamburger-icon"))
        document
            .getElementById("topnav-hamburger-icon")
            .addEventListener("click", toggleHamburgerMenu);
});

</script>
<!--eslint-disable no-mixed-spaces-and-tabs-->
<template>
    <header id="page-topbar">
        <div class="layout-width">
            <div class="navbar-header p-0">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box horizontal-logo">
                        <router-link
                            to="/"
                            class="logo logo-dark"
                        >
                            <span class="logo-sm">
                                <img
                                    src="../../assets/logos/icon.png"
                                    alt=""
                                    height="30"
                                >
                            </span>
                            <span class="logo-lg">
                                <img
                                    src="../../assets/logos/icon.png"
                                    alt=""
                                    height="30"
                                >
                            </span>
                        </router-link>

                        <router-link
                            to="/"
                            class="logo logo-light"
                        >
                            <span class="logo-sm">
                                <img
                                    src="../../assets/logos/icon.png"
                                    alt=""
                                    height="30"
                                >
                            </span>
                            <span class="logo-lg">
                                <img
                                    src="../../assets/logos/icon.png"
                                    alt=""
                                    height="30"
                                >
                            </span>
                        </router-link>
                    </div>

                    <button
                        id="topnav-hamburger-icon"
                        type="button"
                        class="
                          btn btn-soft-sm
                          px-3
                          fs-16
                          header-item
                          vertical-menu-btn
                          topnav-hamburger
                        "
                    >
                        <span class="hamburger-icon">
                            <span/>
                            <span/>
                            <span/>
                        </span>
                    </button>

                </div>

                <div class="d-flex align-items-center">

                    <div class="ms-1 header-item d-none d-sm-flex">
                        <b-button
                            type="button"
                            variant="ghost-secondary"
                            class="btn-icon btn-topbar rounded-circle light-dark-mode"
                            @click="toggleDarkMode"
                        >
                            <i class="bx bx-moon fs-22"/>
                        </b-button>
                    </div>

                    <!-- Notifications Bell -->
                    <NotificationBell />

                    <div class="dropdown ms-sm-3 header-item topbar-user">
                        <button
                            id="page-header-user-dropdown"
                            type="button"
                            class="btn"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >
                            <span class="d-flex align-items-center" v-if="user">
                                <img
                                    v-if="user.avatar"
                                    :src="`${env.url}${user.avatar}`"
                                    alt=""
                                    class="rounded-circle header-profile-user"
                                >
                                <img
                                    v-else
                                    class="rounded-circle header-profile-user"
                                    src="@/assets/images/user.jpg"
                                    alt="Header Avatar"
                                >
                                <span class="text-start ms-xl-2">
                                    <span class=" d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{
                                            user.name
                                                                                                          }}
                                    </span>
                                    <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text text-truncate">
                                        {{
                                            user.position
                                        }}
                                    </span>
                                </span>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <h6 class="dropdown-header">
                                Bem vindo {{ user?.name }}!
                            </h6>

                            <router-link
                                class="dropdown-item"
                                to="/meu-perfil"
                            >
                                <i class="mdi mdi-account text-muted fs-16 align-middle me-1"/>
                                <span class="align-middle">Meu Perfil</span>
                            </router-link>

                            <b-link
                                class="dropdown-item"
                                href="/logout"
                            >
                                <i
                                    class="mdi mdi-logout text-muted fs-16 align-middle me-1"
                                />
                                <span
                                    class="align-middle"
                                    data-key="t-logout"
                                >Sair
                                </span>
                            </b-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<style>

.bg-gray {
    background: #F3F3F9 !important;
}

</style>
