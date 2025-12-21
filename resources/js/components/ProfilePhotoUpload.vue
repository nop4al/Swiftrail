<script setup>
import { ref } from "vue";

const props = defineProps({
    userProfile: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["photo-updated"]);

const selectedFile = ref(null);
const uploading = ref(false);
const uploadError = ref("");
const uploadSuccess = ref(false);
const previewUrl = ref(null);
const fileInput = ref(null);

const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleFileSelect = (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    // Validasi tipe file
    const validTypes = ["image/jpeg", "image/png", "image/jpg", "image/gif"];
    if (!validTypes.includes(file.type)) {
        uploadError.value = "Format file harus JPEG, PNG, JPG, atau GIF";
        return;
    }

    // Validasi ukuran (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        uploadError.value = "Ukuran file maksimal 2MB";
        return;
    }

    selectedFile.value = file;
    uploadError.value = "";

    // Tampilkan preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewUrl.value = e.target?.result;
    };
    reader.readAsDataURL(file);
};

const uploadPhoto = async () => {
    if (!selectedFile.value) {
        uploadError.value = "Pilih foto terlebih dahulu";
        return;
    }

    try {
        uploading.value = true;
        uploadError.value = "";
        uploadSuccess.value = false;

        const token = localStorage.getItem("auth_token");
        const formData = new FormData();
        formData.append("avatar", selectedFile.value);

        const response = await fetch("/api/v1/profile/photo", {
            method: "POST",
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
            },
            body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
            if (data.errors && data.errors.avatar) {
                uploadError.value = data.errors.avatar[0];
            } else {
                uploadError.value = data.message || "Gagal mengunggah foto";
            }
            return;
        }

        uploadSuccess.value = true;
        selectedFile.value = null;
        previewUrl.value = null;

        // Reset file input
        if (fileInput.value) {
            fileInput.value.value = "";
        }

        // Emit event ke parent component
        emit("photo-updated", data.data);

        // Hide success message after 3 seconds
        setTimeout(() => {
            uploadSuccess.value = false;
        }, 3000);
    } catch (error) {
        uploadError.value =
            error.message || "Terjadi kesalahan saat mengunggah foto";
    } finally {
        uploading.value = false;
    }
};

const cancelUpload = () => {
    selectedFile.value = null;
    previewUrl.value = null;
    uploadError.value = "";
    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

const deletePhoto = async () => {
    if (!confirm("Apakah Anda yakin ingin menghapus foto profil?")) {
        return;
    }

    try {
        uploading.value = true;
        uploadError.value = "";

        const token = localStorage.getItem("auth_token");
        const response = await fetch("/api/v1/profile/photo", {
            method: "DELETE",
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
            },
        });

        const data = await response.json();

        if (!response.ok) {
            uploadError.value = data.message || "Gagal menghapus foto";
            return;
        }

        uploadSuccess.value = true;
        emit("photo-updated", { avatar: null });

        setTimeout(() => {
            uploadSuccess.value = false;
        }, 3000);
    } catch (error) {
        uploadError.value =
            error.message || "Terjadi kesalahan saat menghapus foto";
    } finally {
        uploading.value = false;
    }
};

// Save user profile to localStorage only if present (avoid runtime error)
try {
    if (typeof userProfile !== "undefined" && userProfile !== null) {
        localStorage.setItem("userProfile", JSON.stringify(userProfile));
    }
} catch (e) {
    // ignore serialization errors
}
</script>

<template>
    <div class="photo-upload-container">
        <!-- Title -->
        <h3 class="upload-title">Foto Profil</h3>

        <!-- Preview Section -->
        <div class="photo-preview-section">
            <!-- Current or Preview Photo -->
            <div class="photo-display">
                <div v-if="previewUrl" class="photo-preview">
                    <img :src="previewUrl" alt="Preview foto profil" />
                </div>
                <div
                    v-else-if="userProfile?.avatar"
                    class="photo-preview"
                    :style="
                        userProfile?.avatar
                            ? {
                                  backgroundImage: `url(/${userProfile.avatar})`,
                                  backgroundSize: 'cover',
                              }
                            : {}
                    "
                ></div>
                <div v-else class="photo-placeholder">
                    <div class="placeholder-initials">
                        {{ userProfile?.first_name?.charAt(0)
                        }}{{ userProfile?.last_name?.charAt(0) }}
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="photo-actions">
                <button
                    type="button"
                    class="btn-upload"
                    @click="triggerFileInput"
                    :disabled="uploading"
                >
                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M21 15V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V15"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M17 8L12 3L7 8"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M12 3V15"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    Pilih Foto
                </button>

                <button
                    v-if="userProfile?.avatar"
                    type="button"
                    class="btn-delete"
                    @click="deletePhoto"
                    :disabled="uploading"
                >
                    <svg
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M3 6H5H21"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M8 6V4C8 2.89543 8.89543 2 10 2H14C15.1046 2 16 2.89543 16 4V6M19 6V20C19 21.1046 18.1046 22 17 22H7C5.89543 22 5 21.1046 5 20V6H19Z"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M10 11V17"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M14 11V17"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    Hapus Foto
                </button>
            </div>
        </div>

        <!-- File Input (Hidden) -->
        <input
            ref="fileInput"
            type="file"
            accept="image/*"
            style="display: none"
            @change="handleFileSelect"
        />

        <!-- Selected File Info & Upload -->
        <div v-if="selectedFile" class="file-selected-section">
            <div class="file-info">
                <p class="file-name">{{ selectedFile.name }}</p>
                <p class="file-size">
                    {{ (selectedFile.size / 1024).toFixed(2) }} KB
                </p>
            </div>

            <div class="upload-controls">
                <button
                    type="button"
                    class="btn-submit"
                    @click="uploadPhoto"
                    :disabled="uploading"
                >
                    {{ uploading ? "Mengunggah..." : "Unggah Foto" }}
                </button>
                <button
                    type="button"
                    class="btn-cancel"
                    @click="cancelUpload"
                    :disabled="uploading"
                >
                    Batal
                </button>
            </div>
        </div>

        <!-- Messages -->
        <div v-if="uploadError" class="alert alert-error">
            <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <circle
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="2"
                />
                <path
                    d="M12 8V12"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
                <circle cx="12" cy="16" r="1" fill="currentColor" />
            </svg>
            {{ uploadError }}
        </div>

        <div v-if="uploadSuccess" class="alert alert-success">
            <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    d="M20 6L9 17L4 12"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
            {{
                userProfile?.avatar
                    ? "Foto profil berhasil diperbarui!"
                    : "Foto profil berhasil dihapus!"
            }}
        </div>

        <!-- File Requirements -->
        <div class="file-requirements">
            <p class="requirements-title">Persyaratan File:</p>
            <ul class="requirements-list">
                <li>Format: JPEG, PNG, JPG, atau GIF</li>
                <li>Ukuran maksimal: 2MB</li>
                <li>Disarankan: Minimal 200x200 piksel</li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.photo-upload-container {
    padding: 24px;
    background: #f8f9fa;
    border-radius: 12px;
    margin: 20px 0;
}

.upload-title {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 16px;
}

.photo-preview-section {
    display: flex;
    gap: 20px;
    align-items: center;
    margin-bottom: 20px;
}

.photo-display {
    flex: 0 0 auto;
}

.photo-preview,
.photo-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-initials {
    font-size: 48px;
    font-weight: 600;
    color: #0066cc;
    background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
}

.photo-actions {
    display: flex;
    gap: 8px;
    flex-direction: column;
}

.btn-upload,
.btn-delete {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-upload {
    background: #0066cc;
    color: white;
}

.btn-upload:hover:not(:disabled) {
    background: #0052a3;
    box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
}

.btn-delete {
    background: #ff4444;
    color: white;
}

.btn-delete:hover:not(:disabled) {
    background: #cc0000;
    box-shadow: 0 4px 12px rgba(255, 68, 68, 0.3);
}

.btn-upload:disabled,
.btn-delete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.file-selected-section {
    background: white;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 16px;
    border: 1px solid #e0e0e0;
}

.file-info {
    margin-bottom: 12px;
}

.file-name {
    font-size: 14px;
    font-weight: 500;
    color: #1a1a1a;
    margin: 0;
}

.file-size {
    font-size: 12px;
    color: #666;
    margin: 4px 0 0 0;
}

.upload-controls {
    display: flex;
    gap: 8px;
}

.btn-submit,
.btn-cancel {
    flex: 1;
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-submit {
    background: #0066cc;
    color: white;
}

.btn-submit:hover:not(:disabled) {
    background: #0052a3;
}

.btn-cancel {
    background: #f0f0f0;
    color: #1a1a1a;
}

.btn-cancel:hover:not(:disabled) {
    background: #e0e0e0;
}

.btn-submit:disabled,
.btn-cancel:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-error {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ef5350;
}

.alert-success {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #66bb6a;
}

.file-requirements {
    background: white;
    padding: 12px 16px;
    border-radius: 8px;
    border-left: 4px solid #0066cc;
}

.requirements-title {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    margin: 0 0 8px 0;
    letter-spacing: 0.5px;
}

.requirements-list {
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 13px;
    color: #666;
}

.requirements-list li {
    padding: 4px 0;
    padding-left: 20px;
    position: relative;
}

.requirements-list li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #0066cc;
    font-weight: bold;
}

/* Responsive */
@media (max-width: 768px) {
    .photo-preview-section {
        flex-direction: column;
        align-items: flex-start;
    }

    .photo-actions {
        width: 100%;
        flex-direction: row;
    }

    .btn-upload,
    .btn-delete {
        flex: 1;
    }

    .upload-controls {
        flex-direction: column;
    }

    .btn-submit,
    .btn-cancel {
        width: 100%;
    }
}
</style>
