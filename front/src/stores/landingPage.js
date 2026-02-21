import { defineStore } from 'pinia'

export const useLandingPageStore = defineStore('landingPage', {
  state: () => ({
    data: {},
  }),

  actions: {
    setData(payload) {
      this.data = payload
    }
  }
})