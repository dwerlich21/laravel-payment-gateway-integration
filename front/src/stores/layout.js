import { defineStore } from 'pinia'

export const useLayoutStore = defineStore('layout', {
  state: () => ({
    layoutType: 'vertical',
    layoutWidth: 'fluid',
    sidebarSize: 'lg',
    topbar: 'light',
    mode: localStorage.getItem('mode') || 'light',
    position: 'fixed',
    sidebarView: 'default',
    sidebarColor: localStorage.getItem('mode') || 'light',
    sidebarImage: 'none',
    preloader: false,
    visibility: 'show'
  }),

  actions: {
    changeSidebarType(sidebarSize) {
      this.sidebarSize = sidebarSize
    },

    changeTopbar(topbar) {
      this.topbar = topbar
    },

    changeMode(mode) {
      this.mode = mode.mode
      localStorage.setItem('mode', mode.mode)
      
      // Aplica o tema imediatamente
      if (mode.mode === 'dark') {
        document.getElementById('app')?.classList.remove('light')
        document.getElementById('app')?.classList.add('dark')
        document.documentElement.setAttribute("data-layout-mode", "dark")
        document.documentElement.setAttribute("data-sidebar", "dark")
      } else {
        document.getElementById('app')?.classList.remove('dark')
        document.getElementById('app')?.classList.add('light')
        document.documentElement.setAttribute("data-layout-mode", "light")
        document.documentElement.setAttribute("data-sidebar", "light")
      }
    },

    changeSidebarColor(sidebarColor) {
      this.sidebarColor = sidebarColor.sidebarColor
      localStorage.setItem('sidebarColor', sidebarColor.sidebarColor)
      
      // Aplica a cor da sidebar imediatamente
      document.documentElement.setAttribute("data-sidebar", sidebarColor.sidebarColor)
    },

    changeSidebarImage(sidebarImage) {
      this.sidebarImage = sidebarImage
    },

    changeVisibility(visibility) {
      this.visibility = visibility.visibility
    },

    changePreloader(preloader) {
      this.preloader = preloader
    }
  }
})