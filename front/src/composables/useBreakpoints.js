// composables/useBreakpoints.js
import { ref, onMounted, onUnmounted } from 'vue'

export function useBreakpoints() {
    const isXxl = ref(false) // telas ≥1400px
    const isXl = ref(false)  // telas ≥1200px
    const isLg = ref(false)  // telas ≥992px
    const isMd = ref(false)  // telas ≥768px
    const isSm = ref(false)  // telas ≥576px

    const updateBreakpoints = () => {
        const width = window.innerWidth
        isXxl.value = width >= 1400
        isXl.value = width >= 1200
        isLg.value = width >= 992
        isMd.value = width >= 768
        isSm.value = width >= 576
    }

    onMounted(() => {
        updateBreakpoints()
        window.addEventListener('resize', updateBreakpoints)
    })

    onUnmounted(() => {
        window.removeEventListener('resize', updateBreakpoints)
    })

    return {
        isXxl,
        isXl,
        isLg,
        isMd,
        isSm
    }
}