<template>
  <div>
    <div class="login-container">
      <h2>
        <span>{{APP_NAME}}</span><br />
        <span>MINDEF Gym Management System</span>
      </h2>
      <form method="post" @submit.prevent="handleLogin">
        <input
          id="loginId"
          v-model="formData.username"
          required
          type="text"
          placeholder="Login ID"
        />
        <input
          id="password"
          v-model="formData.password"
          required
          type="password"
          placeholder="Password"
        />

        <button
          type="Login"
          :disabled="authStore.isLoading || !isFormValid"
          style="width: 100%"
        >
          {{ authStore.isLoading ? "Processing..." : "Login" }}
        </button>
      </form>
      <div class="register-link">
        <p>
          Don't have an account?
          <a @click="showRegisterForm = true">Register here</a>
        </p>
        <span class="powered-by">Powered by RMA</span>
      </div>
    </div>

    <!-- <AuthRegisterForm v-if="showRegisterForm" @close="showRegisterForm = false" /> -->
  </div>
</template>

<script setup>
import { useAppSettingsStore } from '~/stores/useAppSettingsStore';
import { useAuthStore } from '~/stores/useAuthStore';
import { storeToRefs } from 'pinia';

const authStore = useAuthStore();
const { $notify } = useNuxtApp();
const appSettingsStore = useAppSettingsStore();
const { APP_NAME } = storeToRefs(appSettingsStore); 

const showRegisterForm = ref(false);

const formData = reactive({
  username: "",
  password: "",
});

const handleLogin = async () => {
  if (authStore.isLoading || !isFormValid.value) return;

  try {
    // Kirim data langsung tanpa enkripsi
    await authStore.login({
      username: formData.username,
      password: formData.password
    });

    navigateTo("/dashboard");
  } catch (error) {
    console.error("Login error:", error);
    $notify.error(error?.message || "Login failed. Please check your credentials.");
  }
};

const isFormValid = computed(() => {
  return formData.username.trim() !== "" && formData.password.trim() !== "";
});
</script>

<style lang="scss" scoped>
.login-container {
  position: absolute;
  top: 55%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: var(--card-bg);
  padding: 2.5rem;
  border-radius: 15px;
  box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
  width: 90%;
  max-width: 450px;
  transition: all 0.4s ease-in-out;
  border: 1px solid rgba(255, 255, 255, 0.2);
  animation: fadeIn 1s ease forwards;
  display: flex;
  flex-direction: column;
  align-items: center;
  z-index: 5;

  h2 {
    margin-top: 0;
    text-align: center;
    color: var(--secondary);
    margin-bottom: 1.5rem;
    font-family: "Montserrat", sans-serif;
    font-weight: 700;
    font-size: 28px;

    span {
      font-family: "Montserrat", sans-serif;
      font-size: 2.2rem;
      font-weight: bold;

      &:last-child {
        font-family: "Roboto", sans-serif;
        font-size: 0.9rem;
        font-style: italic;
        color: var(--gray);
      }
    }
  }

  button {
    margin: 1rem 0;
  }

  .register-link {
    text-align: center;
    margin-top: 1.5rem;
    color: var(--dark);

    a {
      color: var(--accent);
      text-decoration: underline;
      font-weight: 600;
      cursor: pointer;
    }
  }

  .powered-by {
    font-family: "Roboto", sans-serif;
    font-size: 0.8rem;
    color: var(--secondary);
    margin-top: 15px;
    font-weight: 500;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translate(-50%, -60%);
  }

  to {
    opacity: 1;
    transform: translate(-50%, -50%);
  }
}
</style>
