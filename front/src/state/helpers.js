// import {computed} from 'vue'
// import {useAuthStore, useLayoutStore, useApiStore} from '@/stores'
//
// // Composables para usar as stores do Pinia
// export const useAuthComputed = () => {
//     const authStore = useAuthStore()
//     return {
//         currentUser: computed(() => authStore.currentUser),
//         loggedIn: computed(() => authStore.loggedIn)
//     }
// }
//
// export const useLayoutComputed = () => {
//     const layoutStore = useLayoutStore()
//     return {
//         layoutType: computed(() => layoutStore.layoutType),
//         sidebarSize: computed(() => layoutStore.sidebarSize),
//         layoutWidth: computed(() => layoutStore.layoutWidth),
//         topbar: computed(() => layoutStore.topbar),
//         mode: computed(() => layoutStore.mode),
//         position: computed(() => layoutStore.position),
//         sidebarView: computed(() => layoutStore.sidebarView),
//         sidebarColor: computed(() => layoutStore.sidebarColor),
//         sidebarImage: computed(() => layoutStore.sidebarImage),
//         visibility: computed(() => layoutStore.visibility)
//     }
// }
//
// export const useApiComputed = () => {
//     const apiStore = useApiStore()
//     return {
//         listAll: computed(() => apiStore.listAll)
//     }
// }
//
// // Para compatibilidade com código existente (Options API)
// export const authComputed = {
//     currentUser: {
//         get() {
//             const authStore = useAuthStore()
//             return authStore.currentUser
//         }
//     },
//     loggedIn: {
//         get() {
//             const authStore = useAuthStore()
//             return authStore.loggedIn
//         }
//     }
// }
//
// export const layoutComputed = {
//     layoutType: {
//         get() {
//             const layoutStore = useLayoutStore()
//             return layoutStore.layoutType
//         }
//     },
//     sidebarSize: {
//         get() {
//             const layoutStore = useLayoutStore()
//             return layoutStore.sidebarSize
//         }
//     },
//     layoutWidth: {
//         get() {
//             const layoutStore = useLayoutStore()
//             return layoutStore.layoutWidth
//         }
//     },
//     topbar: {
//         get() {
//             const layoutStore = useLayoutStore()
//             return layoutStore.topbar
//         }
//     },
//     mode: {
//         get() {
//             const layoutStore = useLayoutStore()
//             return layoutStore.mode
//         }
//     },
//     position: {
//         get() {
//             const layoutStore = useLayoutStore()
//             return layoutStore.position
//         }
//     },
//     sidebarView: {
//         get() {
//             const layoutStore = useLayoutStore()
//             return layoutStore.sidebarView
//         }
//     },
//     sidebarColor: {
//         get() {
//             const layoutStore = useLayoutStore()
//             return layoutStore.sidebarColor
//         }
//     },
//     sidebarImage: {
//         get() {
//             const layoutStore = useLayoutStore()
//             return layoutStore.sidebarImage
//         }
//     },
//     visibility: {
//         get() {
//             const layoutStore = useLayoutStore()
//             return layoutStore.visibility
//         }
//     }
// }
//
// export const authMethods = {
//     logout() {
//         const authStore = useAuthStore()
//         return authStore.logout()
//     },
//     setCurrentUser(user) {
//         const authStore = useAuthStore()
//         return authStore.setCurrentUser(user)
//     },
//     refresh() {
//         const authStore = useAuthStore()
//         return authStore.refresh()
//     }
// }
//
// export const layoutMethods = {
//     changeTopbar(topbar) {
//         const layoutStore = useLayoutStore()
//         return layoutStore.changeTopbar(topbar)
//     },
//     changeMode(mode) {
//         const layoutStore = useLayoutStore()
//         return layoutStore.changeMode(mode)
//     },
//     changeSidebarColor(sidebarColor) {
//         const layoutStore = useLayoutStore()
//         return layoutStore.changeSidebarColor(sidebarColor)
//     },
//     changeSidebarImage(sidebarImage) {
//         const layoutStore = useLayoutStore()
//         return layoutStore.changeSidebarImage(sidebarImage)
//     },
//     changeVisibility(visibility) {
//         const layoutStore = useLayoutStore()
//         return layoutStore.changeVisibility(visibility)
//     }
// }
//
// export const apiMethods = {
//     // Estes métodos devem ser movidos para services
//     getApi() {
//         console.warn('getApi method should be moved to a service')
//     },
//     insert() {
//         console.warn('insert method should be moved to a service')
//     }
// }