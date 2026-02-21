<script setup>
import { onMounted } from 'vue';
import animationData from "@/components/widgets/hzomhqxz.json";
import Lottie from "@/components/widgets/lottie.vue";
import { useAuthStore } from "@/stores/auth.js";

// Data
const defaultOptions = {
    animationData: animationData
};
const authStore = useAuthStore();

// Methods
const logout = () => {
    authStore.logout();
};

// Lifecycle
onMounted(() => {
    logout();
});
</script>

<!--eslint-disable no-mixed-spaces-and-tabs-->
<template>
  <div class="auth-page-wrapper pt-5">
    <div
      id="auth-particles"
      class="auth-one-bg-position auth-one-bg bg-soft-primary"
    />

    <div class="auth-page-content">
      <b-container>
        <b-row class="justify-content-center">
          <b-col
            md="8"
            lg="6"
            xl="5"
          >
            <b-card
              no-body
              class="mt-5"
            >
              <b-card-body class="p-4 text-center">
                  <img
                      src="@/assets/logos/logo-light.png"
                      alt="logo_libertas"
                      height="50"
                  >
                <lottie
                  colors="primary:#405189,secondary:#08a88a"
                  :options="defaultOptions"
                  :height="180"
                  :width="180"
                />
                <div class="mt-4 pt-2">
                  <h5>Você está desconectado.</h5>
                  <p class="text-muted">
                    Faça Login para acessar novamente.
                  </p>
                  <div class="mt-4">
                    <router-link
                      to="/login"
                      class="btn btn-soft-success w-100"
                    >
                      Login
                    </router-link>
                  </div>
                </div>
              </b-card-body>
            </b-card>
          </b-col>
        </b-row>
      </b-container>
    </div>

    <footer class="footer">
      <b-container>
        <b-row>
          <b-col lg="12">
            <div class="text-center">
              <p class="mb-0 text-muted">
                &copy; {{ new Date().getFullYear() }} <a
                  target="_blank"
                  class="text-primary"
                  href="https://lifecode.dev/"
                >LifeCode.</a> Feito com o
                <i class="mdi mdi-heart text-danger" />
              </p>
            </div>
          </b-col>
        </b-row>
      </b-container>
    </footer>
  </div>
</template>

<style>

#auth-particles {
    height: 100%;
}

</style>
