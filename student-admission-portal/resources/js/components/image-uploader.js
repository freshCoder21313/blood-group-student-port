export default (initialValue, deleteUrl, initialIsImage, disabled = false) => ({
    dragover: false,
    previewUrl: initialValue || null,
    deleteUrl: deleteUrl || null,
    fileName: null,
    fileSize: null,
    fileExtension: null,
    isImage: initialIsImage,
    error: null,
    initialValue: initialValue || null,
    disabled: disabled,
    
    init() {
        if (this.previewUrl) {
            if (this.previewUrl === this.initialValue) {
                 this.isImage = initialIsImage;
                 if (!this.isImage) {
                     this.fileExtension = 'FILE';
                 }
            } else {
                this.isImage = this.previewUrl.match(/\.(jpeg|jpg|gif|png)$/) != null;
                if (!this.isImage) {
                     this.fileExtension = 'FILE';
                }
            }
        }
    },
    
    handleDrop(event) {
        this.dragover = false;
        const file = event.dataTransfer.files[0];
        this.processFile(file);
        
        // Manually set input files
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        this.$refs.fileInput.files = dataTransfer.files;
    },
    
    handleFileSelect(event) {
        const file = event.target.files[0];
        this.processFile(file);
    },
    
    processFile(file) {
        if (!file) return;
        
        // Validate size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            this.error = 'File size must be less than 5MB';
            return;
        }
        
        this.error = null;
        this.fileName = file.name;
        this.fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        this.fileExtension = file.name.split('.').pop();
        this.isImage = file.type.startsWith('image/');
        
        const reader = new FileReader();
        reader.onload = (e) => {
            this.previewUrl = e.target.result;
        };
        reader.readAsDataURL(file);
    },
    
    async removeFile() {
        if (this.deleteUrl && this.previewUrl === this.initialValue) {
            if (!confirm('Are you sure you want to delete this file? This cannot be undone.')) {
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(this.deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Deletion failed');
                }
            } catch (e) {
                this.error = 'Failed to delete file. Please try again.';
                return;
            }
        }

        this.previewUrl = null;
        this.fileName = null;
        this.fileSize = null;
        this.$refs.fileInput.value = '';
        this.error = null;
    }
});