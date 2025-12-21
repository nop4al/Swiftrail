<template>
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <div class="logo-icon">
                    <svg
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <rect width="24" height="24" rx="6" fill="#1675E7" />
                        <path
                            d="M6 8C6 6.89543 6.89543 6 8 6H16C17.1046 6 18 6.89543 18 8V14C18 15.1046 17.1046 16 16 16H8C6.89543 16 6 15.1046 6 14V8Z"
                            fill="white"
                        />
                        <rect
                            x="8"
                            y="8"
                            width="3"
                            height="2"
                            rx="0.5"
                            fill="#1675E7"
                        />
                        <rect
                            x="13"
                            y="8"
                            width="3"
                            height="2"
                            rx="0.5"
                            fill="#1675E7"
                        />
                        <circle cx="9" cy="18" r="1.5" fill="white" />
                        <circle cx="15" cy="18" r="1.5" fill="white" />
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-title">SwiftRail</span>
                    <span class="logo-subtitle">SUPER APP</span>
                </div>
            </div>

            <nav class="nav">
                <router-link to="/" class="nav-link">Beranda</router-link>

                <!-- Jika sudah login: tampilkan profile user dengan dropdown -->
                <template v-if="isLoggedIn">
                    <div class="user-profile-menu">
                        <button
                            @click="toggleUserMenu"
                            class="user-profile-btn"
                        >
                            <div
                                class="user-avatar"
                                :style="
                                    userProfile?.avatar
                                        ? {
                                              backgroundImage: `url(/${userProfile.avatar})`,
                                              backgroundSize: 'cover',
                                              backgroundPosition: 'center',
                                          }
                                        : {}
                                "
                            >
                                <span
                                    v-if="!userProfile?.avatar"
                                    class="avatar-initial"
                                    >{{ userInitial }}</span
                                >
                            </div>
                            <div class="user-info">
                                <div class="user-name">{{ userName }}</div>
                            </div>
                            <svg
                                class="menu-icon"
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M7 10L12 15L17 10"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div v-if="showUserMenu" class="dropdown-menu">
                            <router-link to="/ticket" class="dropdown-item">
                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M7 3H17C18.1046 3 19 3.89543 19 5V19C19 20.1046 18.1046 21 17 21H7C5.89543 21 5 20.1046 5 19V5C5 3.89543 5.89543 3 7 3Z"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    />
                                    <path
                                        d="M9 7H15M9 11H15M9 15H13"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                Tiket Saya
                            </router-link>
                            <router-link to="/profile" class="dropdown-item">
                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <circle
                                        cx="12"
                                        cy="8"
                                        r="4"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    />
                                    <path
                                        d="M4 20C4 16.134 7.58172 13 12 13C16.4183 13 20 16.134 20 20"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                Profile
                            </router-link>

                            <!-- Admin Panel - hanya untuk admin -->
                            <div v-if="isAdmin" class="dropdown-divider"></div>
                            <router-link
                                v-if="isAdmin"
                                to="/admin"
                                class="dropdown-item admin-item"
                            >
                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <rect
                                        x="3"
                                        y="3"
                                        width="8"
                                        height="8"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    />
                                    <rect
                                        x="13"
                                        y="3"
                                        width="8"
                                        height="8"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    />
                                    <rect
                                        x="3"
                                        y="13"
                                        width="8"
                                        height="8"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    />
                                    <rect
                                        x="13"
                                        y="13"
                                        width="8"
                                        height="8"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    />
                                </svg>
                                Admin Panel
                            </router-link>

                            <button
                                @click="handleLogout"
                                class="dropdown-item logout-item"
                            >
                                <svg
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M19 12H5M5 12L12 19M5 12L12 5"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                Logout
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Jika belum login: tampilkan Login & Register -->
                <template v-else>
                    <router-link to="/login" class="nav-link"
                        >Masuk</router-link
                    >
                    <router-link
                        to="/register"
                        class="nav-link nav-link-register"
                        >Daftar</router-link
                    >
                </template>
            </nav>
        </div>
    </header>
</template>

<script setup>
import { useRouter } from "vue-router";
import { computed, ref, onMounted, onUnmounted, watch } from "vue";

const router = useRouter();
const authToken = ref(localStorage.getItem("auth_token"));
const showUserMenu = ref(false);
const userProfile = ref({
    first_name: "",
    last_name: "",
    user_id: "",
    avatar: null,
});

// Check apakah sudah login dari localStorage
const isLoggedIn = computed(() => {
    return !!authToken.value;
});

const userName = computed(() => {
    const profile = userProfile.value;
    if (profile && profile.first_name && profile.last_name) {
        return `${profile.first_name} ${profile.last_name}`;
    }
    return "User";
});

const userInitial = computed(() => {
    const profile = userProfile.value;
    if (profile && profile.first_name && profile.last_name) {
        return (
            profile.first_name.charAt(0) + profile.last_name.charAt(0)
        ).toUpperCase();
    }
    if (profile && profile.first_name) {
        return profile.first_name.charAt(0).toUpperCase();
    }
    return "U";
});

const userId = computed(() => {
    return userProfile.value?.user_id || "ID tidak tersedia";
});

const isAdmin = computed(() => {
    return userProfile.value?.role === "admin";
});

// Load user profile dari localStorage
const loadUserProfile = () => {
    const profile = localStorage.getItem("userProfile");
    if (profile) {
        try {
            const parsed = JSON.parse(profile);
            if (parsed) {
                userProfile.value = {
                    first_name: parsed.first_name || "",
                    last_name: parsed.last_name || "",
                    user_id: parsed.user_id || "",
                    avatar: parsed.avatar || null,
                };
                console.log("Loaded profile:", userProfile.value);
            }
        } catch (e) {
            console.error("Error parsing userProfile:", e);
        }
    }
};

// Toggle user menu
const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
};

// Handle logout
const handleLogout = async () => {
    try {
        const token = localStorage.getItem("auth_token");

        // Call logout endpoint
        await fetch("/api/v1/auth/logout", {
            method: "POST",
            headers: {
                Authorization: `Bearer ${token}`,
                "Content-Type": "application/json",
            },
        }).catch(() => {}); // Ignore error, logout anyway
    } catch (error) {
        console.error("Logout error:", error);
    } finally {
        // Clear localStorage
        localStorage.removeItem("auth_token");
        localStorage.removeItem("userProfile");
        localStorage.removeItem("userData");

        // Trigger event untuk update components
        window.dispatchEvent(new Event("auth-changed"));

        // Reset state
        authToken.value = null;
        showUserMenu.value = false;

        // Redirect to login
        router.push("/login");
    }
};

// Close menu when clicking outside
const handleClickOutside = (e) => {
    const menu = document.querySelector(".user-profile-menu");
    if (menu && !menu.contains(e.target)) {
        showUserMenu.value = false;
    }
};

// Listen untuk perubahan storage (dari tab lain atau login/logout)
onMounted(() => {
    loadUserProfile();

    // Debug: watch untuk perubahan profile
    watch(
        () => userProfile.value,
        (newVal) => {
            console.log("userProfile changed:", newVal);
        },
        { deep: true }
    );

    window.addEventListener("storage", (e) => {
        if (e.key === "authToken") {
            authToken.value = e.newValue;
        }
        if (e.key === "userProfile") {
            loadUserProfile();
        }
    });

    // Listen juga untuk perubahan dari halaman yang sama (custom event)
    window.addEventListener("auth-changed", (e) => {
        authToken.value = localStorage.getItem("auth_token");
        loadUserProfile();
    });

    // Listen untuk profile update (avatar upload, dll) dari halaman yang sama
    window.addEventListener("profile-updated", (e) => {
        loadUserProfile();
    });

    // Close menu on outside click
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.header {
    background: white;
    color: #1a1a1a;
    padding: 0.75rem 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-bottom: 1px solid #e5e7eb;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    cursor: pointer;
    text-decoration: none;
    color: #1a1a1a;
    flex-shrink: 0;
}

.logo-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: #f0f4ff;
    border-radius: 6px;
}

.logo-text {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
}

.logo-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1675e7;
    letter-spacing: -0.5px;
}

.logo-subtitle {
    font-size: 0.65rem;
    font-weight: 600;
    color: #6b7280;
    letter-spacing: 0.8px;
}

.nav {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.nav-link {
    color: #4b5563;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    position: relative;
    white-space: nowrap;
}

.nav-link:hover {
    color: #1675e7;
    background: #f0f4ff;
}

.nav-link.router-link-active {
    color: #1675e7;
    background: #f0f4ff;
    font-weight: 600;
}

.nav-link-register {
    background: #1675e7;
    color: white !important;
    font-weight: 600;
    padding: 0.4rem 1.2rem !important;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.nav-link-register:hover {
    background: #1563d1 !important;
    box-shadow: 0 2px 8px rgba(22, 117, 231, 0.3);
}

/* User Profile Menu Styles */
.user-profile-menu {
    position: relative;
}

.user-profile-btn {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.4rem 0.8rem;
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: inherit;
}

.user-profile-btn:hover {
    background: #f0f4ff;
    border-color: #1675e7;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #1675e7;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    flex-shrink: 0;
    overflow: hidden;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    text-align: left;
}

.user-name {
    font-weight: 600;
    font-size: 0.85rem;
    color: #1a1a1a;
    white-space: nowrap;
}

.menu-icon {
    color: #4b5563;
    transition: transform 0.3s ease;
}

.user-profile-btn:hover .menu-icon {
    color: #1675e7;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    min-width: 180px;
    z-index: 1001;
    overflow: hidden;
    animation: slideDown 0.2s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    width: 100%;
    padding: 0.75rem 1rem;
    background: none;
    border: none;
    color: #4b5563;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: inherit;
    text-align: left;
}

.dropdown-item:hover {
    background: #f0f4ff;
    color: #1675e7;
}

.dropdown-item svg {
    color: currentColor;
    flex-shrink: 0;
}

.logout-item {
    color: #f97316;
    border-top: 1px solid #e5e7eb;
    margin-top: 0.5rem;
    padding-top: 0.75rem;
}

.logout-item:hover {
    background: #fef2f2;
    color: #ea580c;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

.admin-item {
    color: #7c3aed;
    font-weight: 600;
}

.admin-item:hover {
    background: #faf5ff;
    color: #6d28d9;
}

@media (max-width: 768px) {
    .header-container {
        padding: 0 1rem;
        flex-direction: column;
        gap: 0.75rem;
    }

    .nav {
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .nav-link {
        font-size: 0.8rem;
        padding: 0.35rem 0.7rem;
    }

    .logo-title {
        font-size: 0.95rem;
    }

    .logo-subtitle {
        font-size: 0.6rem;
    }

    .user-id {
        max-width: 120px;
    }

    .dropdown-menu {
        min-width: 160px;
    }

    .user-profile-btn {
        gap: 0.5rem;
    }

    .user-avatar {
        width: 28px;
        height: 28px;
        font-size: 0.8rem;
    }

    .user-name {
        font-size: 0.8rem;
    }

    .user-id {
        font-size: 0.65rem;
    }
}
</style>
