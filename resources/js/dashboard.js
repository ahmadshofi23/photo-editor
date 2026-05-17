/**
 * Dashboard Alpine.js components & helpers.
 * Registered via Alpine.data() so they are available when Alpine initialises.
 */

// Alpine registers components before it starts (alpine:init fires before DOM walk)
document.addEventListener('alpine:init', () => {
    window.Alpine.data('uploadZone', () => ({
        dragover:      false,
        uploading:     false,
        progress:      0,
        uploadedImage: null,
        uploadedFileName: '',
        uploadedFileUrl: '',

        handleDrop(e) {
            this.dragover = false;
            if (e.dataTransfer.files.length) this.uploadFile(e.dataTransfer.files[0]);
        },

        handleFileSelect(e) {
            if (e.target.files.length) this.uploadFile(e.target.files[0]);
        },

        uploadFile(file) {
            if (!file.type.startsWith('image/')) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message: 'Must be an image file.', type: 'error' },
                }));
                return;
            }

            // DO NOT set Content-Type manually — browser must include the
            // multipart boundary automatically when using FormData.
            const formData = new FormData();
            formData.append('image', file);

            this.uploading = true;
            this.progress  = 0;
            this.uploadedImage = null; // Reset previous state

            window.axios.post('/api/v1/upload', formData, {
                onUploadProgress: (e) => {
                    this.progress = e.total
                        ? Math.round((e.loaded * 100) / e.total)
                        : 50;
                },
            })
            .then((response) => {
                this.uploadedImage = response.data.data.id;
                this.uploadedFileName = file.name;
                this.uploadedFileUrl = URL.createObjectURL(file);
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message: 'Upload successful!', type: 'success' },
                }));
            })
            .catch((error) => {
                const msg =
                    error.response?.data?.errors ||
                    error.response?.data?.message ||
                    'Upload failed. Please try again.';
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message: msg, type: 'error' },
                }));
                console.error('[Upload Error]', error.response?.data);
            })
            .finally(() => {
                this.uploading = false;
            });
        },
    }));
});

// ─── Global helpers (called from x-on:click in blade) ────────────────────────

window.downloadImage = function (id, type) {
    window.axios
        .get(`/api/v1/download/${id}?type=${type}`)
        .then((res) => {
            const url = res.data?.data?.download_url || res.data?.download_url;
            if (url) window.location.href = url;
        })
        .catch(() =>
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { message: 'Download failed.', type: 'error' },
            }))
        );
};

window.deleteImage = function (id) {
    if (!confirm('Are you sure you want to delete this image?')) return;

    window.axios
        .delete(`/api/v1/images/${id}`)
        .then(() => {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { message: 'Deleted successfully.', type: 'success' },
            }));
            setTimeout(() => window.location.reload(), 800);
        })
        .catch(() =>
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { message: 'Failed to delete.', type: 'error' },
            }))
        );
};
