<script setup>
import {ref, computed, onMounted, onUnmounted, watch, nextTick, defineExpose} from 'vue';
import {useLayoutStore} from '@/stores';
import {useRouter} from 'vue-router';
import {SimpleBar} from "simplebar-vue3";

// Componentes
import NavBar from "@/components/layouts/nav-bar.vue";
import Menu from "@/components/layouts/menu.vue";
import Footer from "@/components/layouts/footer.vue";
import Spinner from "@/components/base/Spinner.vue";

// Composables
import {useGlobalSpinner} from '@/composables/useGlobalSpinner';

// Store e Router
const layoutStore = useLayoutStore();
const router = useRouter();

// Spinner global
const {spinnerState} = useGlobalSpinner();

// Estado reativo
const isMenuCondensed = ref(false); // estado do menu condensado
// const scrollbar = ref(null); // referência removida pois não está sendo usada e causa warning no simplebar-vue3
const observer = ref(null); // observer para mudanças nos data attributes

// Configuração inicial
localStorage.setItem('hoverd', false);

const sidebarColor = computed({ // cor da sidebar
    get() {
        return localStorage.getItem('mode') ? localStorage.getItem('mode') : 'light';
    },
    set(value) {
        changeSidebarColor({sidebarColor: value});
    },
});

const mode = computed({ // modo claro/escuro
    get() {
        return localStorage.getItem('mode') ? localStorage.getItem('mode') : 'light';
    },
    set(value) {
        changeMode({mode: value});
    },
});

const visibility = computed({ // visibilidade da sidebar
    get() {
        return layoutStore.visibility;
    },
    set(value) {
        if (value === 'hidden') {
            const hamburgerIcon = document.querySelector(".hamburger-icon");
            if (hamburgerIcon) {
                hamburgerIcon.classList.add("open");
            }
        } else {
            const hamburgerIcon = document.querySelector(".hamburger-icon");
            if (hamburgerIcon) {
                hamburgerIcon.classList.remove("open");
            }
        }
        changeVisibility({visibility: value});
    },
});

// Métodos do store
const changeSidebarColor = (payload) => { // mudar cor da sidebar
    layoutStore.changeSidebarColor(payload);
};

const changeMode = (payload) => { // mudar modo claro/escuro
    layoutStore.changeMode(payload);
};

const changeVisibility = (payload) => { // mudar visibilidade da sidebar
    layoutStore.changeVisibility(payload);
};

// Métodos da interface
const topFunction = () => { // voltar ao topo da página
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
};

const resizeWindow = () => { // redimensionar janela
    const windowSize = document.documentElement.clientWidth;

    if (windowSize >= 1025) {
        if (document.documentElement.getAttribute("data-layout") === "vertical") {
            document.documentElement.setAttribute("data-sidebar-size", layoutStore.sidebarSize);
        }
        if (document.documentElement.getAttribute("data-layout") === "semibox") {
            document.documentElement.setAttribute("data-sidebar-size", layoutStore.sidebarSize);
        }
        if (document.documentElement.getAttribute("data-sidebar-visibility") === "show") {
            const hamburgerIcon = document.querySelector(".hamburger-icon");
            if (hamburgerIcon) {
                hamburgerIcon.classList.remove("open");
            }
        }
    } else if (windowSize < 1025 && windowSize > 767) {
        document.body.classList.remove("twocolumn-panel");
        if (document.documentElement.getAttribute("data-layout") === "vertical") {
            document.documentElement.setAttribute("data-sidebar-size", "sm");
        }
        if (document.documentElement.getAttribute("data-layout") === "semibox") {
            document.documentElement.setAttribute("data-sidebar-size", "sm");
        }
        const hamburgerIcon = document.querySelector(".hamburger-icon");
        if (hamburgerIcon) {
            hamburgerIcon.classList.add("open");
        }
    } else if (windowSize <= 767) {
        document.body.classList.remove("vertical-sidebar-enable");
        document.body.classList.add("twocolumn-panel");
        if (document.documentElement.getAttribute("data-layout") !== "horizontal") {
            document.documentElement.setAttribute("data-sidebar-size", "lg");
        }
        const hamburgerIcon = document.querySelector(".hamburger-icon");
        if (hamburgerIcon) {
            hamburgerIcon.classList.add("open");
        }
    }
};

const initActiveMenu = () => { // inicializar menu ativo
    const currentSize = document.documentElement.getAttribute('data-sidebar-size');

    if (currentSize === 'sm-hover') {
        localStorage.setItem('hoverd', 'true');
        document.documentElement.setAttribute('data-sidebar-size', 'sm-hover-active');
    } else if (currentSize === 'sm-hover-active') {
        localStorage.setItem('hoverd', 'false');
        document.documentElement.setAttribute('data-sidebar-size', 'sm-hover');
    } else {
        document.documentElement.setAttribute('data-sidebar-size', 'sm-hover');
    }
};

const toggleMenu = () => { // alternar menu
    document.body.classList.toggle("sidebar-enable");

    if (window.screen.width >= 992) {
        router.afterEach(() => {
            document.body.classList.remove("sidebar-enable");
            document.body.classList.remove("vertical-collpsed");
        });
        document.body.classList.toggle("vertical-collpsed");
    } else {
        router.afterEach(() => {
            document.body.classList.remove("sidebar-enable");
        });
        document.body.classList.remove("vertical-collpsed");
    }
    isMenuCondensed.value = !isMenuCondensed.value;
};

const toggleRightSidebar = () => { // alternar sidebar direita
    document.body.classList.toggle("right-bar-enabled");
};

const hideRightSidebar = () => { // esconder sidebar direita
    document.body.classList.remove("right-bar-enabled");
};

const setupBackToTop = () => { // configurar botão voltar ao topo
    const backtoTop = document.getElementById("back-to-top");

    if (backtoTop) {
        window.onscroll = function () {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                backtoTop.style.display = "block";
            } else {
                backtoTop.style.display = "none";
            }
        };
    }
};

const setupSidebarGradient = () => { // configurar gradiente da sidebar
    Array.from(document.querySelectorAll("[name='data-sidebar']")).forEach(function (elem) {
        const collapseTarget = document.querySelector("[data-bs-target='#collapseBgGradient']");

        if (collapseTarget) {
            const checkedInput = document.querySelector("#collapseBgGradient .form-check input:checked");

            if (checkedInput) {
                collapseTarget.classList.add("active");
            } else {
                collapseTarget.classList.remove("active");
                const collapseElement = document.getElementById('collapseBgGradient');
                if (collapseElement) {
                    collapseElement.classList.remove('show');
                }
            }

            elem.addEventListener("change", function () {
                const checkedInput = document.querySelector("#collapseBgGradient .form-check input:checked");

                if (checkedInput) {
                    collapseTarget.classList.add("active");
                } else {
                    const collapseElement = document.getElementById('collapseBgGradient');
                    if (collapseElement) {
                        collapseElement.classList.remove('show');
                    }
                    collapseTarget.classList.remove("active");
                }
            });
        }
    });
};

const updateSidebarWidth = () => { // atualizar largura da sidebar para o spinner
    const sidebarSize = document.documentElement.getAttribute('data-sidebar-size');
    const layout = document.documentElement.getAttribute('data-layout');

    let sidebarWidth = '0px';

    if (layout !== 'horizontal') {
        switch (sidebarSize) {
            case 'sm':
            case 'sm-hover':
                sidebarWidth = '70px';
                break;
            case 'md':
                sidebarWidth = '180px';
                break;
            case 'lg':
            case 'sm-hover-active':
            default:
                sidebarWidth = '250px';
                break;
        }
    }

    document.documentElement.style.setProperty('--sidebar-width', sidebarWidth);
};

// Watchers - agora a lógica está na store
watch(mode, () => {
    // A lógica de aplicação do tema agora está na mutation CHANGE_MODE da store
    // Este watcher só é mantido para compatibilidade
}, {immediate: true, deep: true});

watch(sidebarColor, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        switch (newVal) {
            case "dark":
                document.documentElement.setAttribute("data-sidebar", "dark");
                break;
            case "light":
                document.documentElement.setAttribute("data-sidebar", "light");
                break;
            case "gradient":
                document.documentElement.setAttribute("data-sidebar", "gradient");
                break;
            case "gradient-2":
                document.documentElement.setAttribute("data-sidebar", "gradient-2");
                break;
            case "gradient-3":
                document.documentElement.setAttribute("data-sidebar", "gradient-3");
                break;
            case "gradient-4":
                document.documentElement.setAttribute("data-sidebar", "gradient-4");
                break;
        }
    }
}, {immediate: true, deep: true});

watch(() => layoutStore.layoutType, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        switch (newVal) {
            case "horizontal":
                document.documentElement.setAttribute("data-layout", "horizontal");
                break;
            case "vertical":
                document.documentElement.setAttribute("data-layout", "vertical");
                break;
            case "twocolumn":
                document.documentElement.setAttribute("data-layout", "twocolumn");
                break;
            case "semibox":
                document.documentElement.setAttribute("data-layout", "semibox");
                break;
        }
    }
}, {immediate: true, deep: true});

watch(visibility, (newVal, oldVal) => {
    if (newVal !== oldVal) {
        switch (newVal) {
            case "show":
                document.documentElement.setAttribute("data-sidebar-visibility", "show");
                break;
            case "hidden":
                document.documentElement.setAttribute("data-sidebar-visibility", "hidden");
                break;
        }
    }
}, {immediate: true, deep: true});

// Lifecycle hooks
onMounted(async () => {
    // Aguarda o próximo tick para garantir que o DOM está montado
    await nextTick();

    // Remove atributos do body
    document.body.removeAttribute("data-layout");
    document.body.removeAttribute("data-topbar");
    document.body.removeAttribute("data-layout-size");

    // Configura hover da sidebar
    if (localStorage.getItem('hoverd') === 'true') {
        document.documentElement.setAttribute('data-sidebar-size', 'sm-hover-active');
    }

    // Configura overlay click
    const overlay = document.getElementById('overlay');
    if (overlay) {
        overlay.addEventListener('click', () => {
            document.body.classList.remove('vertical-sidebar-enable');
        });
    }

    // Configura botão voltar ao topo
    setupBackToTop();

    // Configura gradiente da sidebar
    setupSidebarGradient();

    // Adiciona listener para redimensionamento
    window.addEventListener("resize", resizeWindow);

    // Configura largura da sidebar para o spinner
    updateSidebarWidth();

    // Força a aplicação do tema inicial baseado na store
    const currentMode = layoutStore.mode;
    layoutStore.changeMode({mode: currentMode});

    // Observer para mudanças nos data attributes (para o spinner)
    observer.value = new MutationObserver(updateSidebarWidth);
    observer.value.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-sidebar-size', 'data-layout', 'data-sidebar-visibility']
    });
});

onUnmounted(() => {
    // Remove event listeners
    window.removeEventListener("resize", resizeWindow);

    // Desconecta observer
    if (observer.value) {
        observer.value.disconnect();
    }
});

// Expõe métodos para serem usados no template (se necessário)
defineExpose({
    toggleMenu,
    toggleRightSidebar,
    hideRightSidebar,
    initActiveMenu,
    topFunction
});
</script>

<template>
    <div id="layout-wrapper">
        <NavBar/>
        <div>
            <!-- ========== Left Sidebar Start ========== -->
            <!-- ========== App Menu ========== -->
            <div class="app-menu navbar-menu">
                <!-- LOGO -->
                <div class="navbar-brand-box">

                    <!-- Light Logo-->
                    <router-link
                        to="/"
                        class="logo logo-dark"
                    >
                        <span class="logo-sm">
                            <img
                                src="../../assets/logos/icon.png"
                                alt=""
                                height="40"
                            >
                        </span>
                        <span class="logo-lg">
                            <img
                                src="../../assets/logos/logo-light.png"
                                alt=""
                                height="40"
                            >
                        </span>
                    </router-link>

                    <!-- Dark Logo-->
                    <router-link
                        to="/"
                        class="logo logo-light"
                    >
                        <span class="logo-sm">
                            <img
                                src="../../assets/logos/icon-dark.png"
                                alt=""
                                height="40"
                            >
                        </span>
                        <span class="logo-lg">
                            <img
                                src="../../assets/logos/logo-dark.png"
                                alt=""
                                height="40"
                            >
                        </span>
                    </router-link>
                    <button
                        id="vertical-hover"
                        type="button"
                        class="btn btn-soft-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
                        @click="initActiveMenu"
                    >
                        <i class="ri-record-circle-line"/>
                    </button>
                </div>

                <SimpleBar
                    id="scrollbar"
                    class="h-100"
                >
                    <Menu/>

                </SimpleBar>
                <div class="sidebar-background"/>
            </div>
            <!-- Left Sidebar End -->
            <!-- Vertical Overlay-->
            <div
                id="overlay"
                class="vertical-overlay"
            />
        </div>
        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="main-content">
            <div class="page-content">
                <!-- Start Content-->
                <b-container
                    fluid
                    class="position-relative"
                >
                    <slot/>
                </b-container>
            </div>
            <Footer/>
        </div>
        <div>
            <b-button
                id="back-to-top"
                variant="danger"
                class="btn-icon"
                @click="topFunction"
            >
                <i class="ri-arrow-up-line"/>
            </b-button>
        </div>

        <!-- Spinner - Agora usando o novo componente -->
        <Spinner
            :show="spinnerState.show"
            :message="spinnerState.message"
            :sub-message="spinnerState.subMessage"
            :overlay="spinnerState.overlay"
            :z-index="spinnerState.zIndex"
        />
    </div>
</template>