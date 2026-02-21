<script setup>
import {onMounted} from 'vue';
import {useRoute} from 'vue-router';
import env from '@/env';

const route = useRoute();

// Methods
const onRoutechange = (newRoute) => {
    initActiveMenu(newRoute.path);
    if (document.getElementsByClassName("mm-active").length > 0) {
        const currentPosition = document.getElementsByClassName("mm-active")[0].offsetTop;
        if (currentPosition > 500) {
            // Note: Simplebar ref handling would need to be implemented if used
        }
    }
};

const initActiveMenu = (path) => {
    setTimeout(() => {
        if (document.querySelector("#navbar-nav")) {
            // Remove todas as classes active existentes
            document.querySelectorAll("#navbar-nav .active").forEach(item => {
                item.classList.remove("active");
            });

            // Remove aria-expanded dos menus colapsáveis
            document.querySelectorAll("#navbar-nav [aria-expanded='true']").forEach(item => {
                item.setAttribute("aria-expanded", "false");
            });

            // Remove classe show dos menus colapsáveis
            document.querySelectorAll("#navbar-nav .collapse.show").forEach(item => {
                item.classList.remove("show");
            });

            // Tenta encontrar link exato primeiro
            let a = document.querySelector("#navbar-nav").querySelector('[href="' + path + '"]');

            // Se não encontrar link exato, procura por links parciais (para rotas filhas)
            if (!a) {
                // Mapeia rotas principais com suas rotas filhas
                const routeMapping = {
                    '/usuários': ['/usuários/cadastrar', '/usuários/detalhes', '/usuários/editar'],
                    '/metas': ['/metas/cadastrar', '/metas/detalhes', '/metas/editar'],
                    '/audit-logs': [],
                };

                // Verifica se a rota atual é uma rota filha
                for (const [parentRoute, childRoutes] of Object.entries(routeMapping)) {
                    if (childRoutes.some(childRoute => path.startsWith(childRoute))) {
                        a = document.querySelector("#navbar-nav").querySelector('[href="' + parentRoute + '"]');
                        break;
                    }
                }
            }

            if (a) {
                a.classList.add("active");
                let parentCollapseDiv = a.closest(".collapse.menu-dropdown");
                if (parentCollapseDiv) {
                    parentCollapseDiv.classList.add("show");
                    parentCollapseDiv.parentElement.children[0].classList.add("active");
                    parentCollapseDiv.parentElement.children[0].setAttribute("aria-expanded", "true");
                    if (parentCollapseDiv.parentElement.closest(".collapse.menu-dropdown")) {
                        parentCollapseDiv.parentElement.closest(".collapse").classList.add("show");
                        if (parentCollapseDiv.parentElement.closest(".collapse").previousElementSibling)
                            parentCollapseDiv.parentElement.closest(".collapse").previousElementSibling.classList.add("active");
                        const grandparent = parentCollapseDiv.parentElement.closest(".collapse").previousElementSibling.parentElement.closest(".collapse");
                        if (grandparent && grandparent && grandparent.previousElementSibling) {
                            grandparent.previousElementSibling.classList.add("active");
                            grandparent.classList.add("show");
                        }
                    }
                }
            }
        }
    }, 0);
};

// Lifecycle
onMounted(() => {
    if (document.querySelectorAll(".navbar-nav .collapse")) {
        let collapses = document.querySelectorAll(".navbar-nav .collapse");

        collapses.forEach((collapse) => {
            // Hide sibling collapses on `show.bs.collapse`
            collapse.addEventListener("show.bs.collapse", (e) => {
                e.stopPropagation();
                let closestCollapse = collapse.parentElement.closest(".collapse");
                if (closestCollapse) {
                    let siblingCollapses =
                        closestCollapse.querySelectorAll(".collapse");
                    siblingCollapses.forEach((siblingCollapse) => {
                        if (siblingCollapse.classList.contains("show")) {
                            siblingCollapse.classList.remove("show");
                            siblingCollapse.parentElement.firstChild.setAttribute("aria-expanded", "false");
                        }
                    });
                } else {
                    let getSiblings = (elem) => {
                        // Setup siblings array and get the first sibling
                        let siblings = [];
                        let sibling = elem.parentNode.firstChild;
                        // Loop through each sibling and push to the array
                        while (sibling) {
                            if (sibling.nodeType === 1 && sibling !== elem) {
                                siblings.push(sibling);
                            }
                            sibling = sibling.nextSibling;
                        }
                        return siblings;
                    };
                    let siblings = getSiblings(collapse.parentElement);
                    siblings.forEach((item) => {
                        if (item.childNodes.length > 2) {
                            item.firstElementChild.setAttribute("aria-expanded", "false");
                            item.firstElementChild.classList.remove("active");
                        }
                        let ids = item.querySelectorAll("*[id]");
                        ids.forEach((item1) => {
                            item1.classList.remove("show");
                            item1.parentElement.firstChild.setAttribute("aria-expanded", "false");
                            item1.parentElement.firstChild.classList.remove("active");
                            if (item1.childNodes.length > 2) {
                                let val = item1.querySelectorAll("ul li a");

                                val.forEach((subitem) => {
                                    if (subitem.hasAttribute("aria-expanded"))
                                        subitem.setAttribute("aria-expanded", "false");
                                });
                            }
                        });
                    });
                }
            });

            // Hide nested collapses on `hide.bs.collapse`
            collapse.addEventListener("hide.bs.collapse", (e) => {
                e.stopPropagation();
                let childCollapses = collapse.querySelectorAll(".collapse");
                childCollapses.forEach((childCollapse) => {
                    let childCollapseInstance = childCollapse;
                    childCollapseInstance.classList.remove("show");
                    childCollapseInstance.parentElement.firstChild.setAttribute("aria-expanded", "false");
                });
            });
        });
    }

    // Inicializa o menu ativo
    onRoutechange(route);
});

// Watch route changes
import {watch} from 'vue';

watch(route, (newRoute) => {
    onRoutechange(newRoute);
}, {immediate: true, deep: true});

</script>

<template>
    <b-container fluid>
        <ul
            id="navbar-nav"
            class="navbar-nav h-100"
        >
            <li v-permission="'dashboard.index'" class="nav-item">
                <router-link
                    class="nav-link menu-link"
                    to="/"
                >
                    <i class="mdi mdi-speedometer"/>
                    <span>Dashboard</span>
                </router-link>
            </li>

            <li
                v-permission="['users.index', 'audit-logs.index']"
                class="nav-item"
            >
                <a
                    class="nav-link menu-link"
                    href="#managementSection"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="managementSection"
                >
                    <i class="mdi mdi-cogs"/>
                    <span>Gerenciar</span>
                </a>
                <div
                    id="managementSection"
                    class="collapse menu-dropdown"
                >
                    <ul class="nav nav-sm flex-column">
                        <li
                            v-permission="'users.index'"
                            class="nav-item"
                        >
                            <router-link
                                to="/usuarios"
                                class="nav-link custom-abc"
                            >
                                Usuários
                            </router-link>
                        </li>
                        <li
                            v-permission="'audit-logs.index'"
                            class="nav-item"
                        >
                            <router-link
                                to="/audit-logs"
                                class="nav-link"
                            >
                                Logs de Auditoria
                            </router-link>
                        </li>
                    </ul>
                </div>
            </li>

            <li v-permission="'notifications.index'" class="nav-item">
                <router-link
                    class="nav-link menu-link"
                    to="/notificacoes"
                >
                    <i class="mdi mdi-bell-outline"/>
                    <span>Notificações</span>
                </router-link>
            </li>

        </ul>
    </b-container>
</template>

<style>

.menu-link.active {
    font-weight: bold;
}

.nav-link.active {
    font-weight: 600;
}
</style>
