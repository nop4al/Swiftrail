<script setup>
import { onMounted, ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import Header from "./components/Header.vue";
import Footer from "./components/Footer.vue";
import AuthWarningModal from "./components/modals/AuthWarningModal.vue";

const router = useRouter();
const route = useRoute();
const authWarningRef = ref(null);

// Check apakah route saat ini adalah login atau register atau admin
const hideHeaderFooter = computed(() => {
    return (
        route.path === "/login" ||
        route.path === "/register" ||
        route.path === "/admin"
    );
});

onMounted(() => {
    fetch("/api/v1/health")
        .then((r) => r.json())
        .then((data) => {
            console.log("API Status:", data);
        })
        .catch((error) => {
            console.error("API Error:", error);
        });
});

// Provide method to trigger warning from router guard
window.showAuthWarning = () => {
    if (authWarningRef.value) {
        authWarningRef.value.showWarning();
    }
};
</script>

<template>
    <div class="app">
        <Header v-if="!hideHeaderFooter" />
        <main class="app-main">
            <AuthWarningModal ref="authWarningRef" />
            <router-view />
        </main>
        <Footer v-if="!hideHeaderFooter" />
    </div>
</template>

<style>
/* Global styles are in style.css */
.app {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.app-main {
    flex: 1;
}
</style>
