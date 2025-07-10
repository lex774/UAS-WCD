// File upload logo untuk post-job

document.addEventListener('DOMContentLoaded', function() {
    const logoInput = document.getElementById('company-logo-file');
    const logoUploadContainer = document.getElementById('logoUploadContainer');
    const logoFilePreview = document.getElementById('logoFilePreview');
    const logoFileName = document.getElementById('logoFileName');
    const logoFileSize = document.getElementById('logoFileSize');
    const logoUploadError = document.getElementById('logoUploadError');

    // Click to open file dialog
    logoUploadContainer.addEventListener('click', function(e) {
        if (e.target.tagName !== 'BUTTON') {
            logoInput.click();
        }
    });

    // File input change
    logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) handleLogoFileSelect(file);
    });

    // Drag and drop
    logoUploadContainer.addEventListener('dragover', function(e) {
        e.preventDefault();
        logoUploadContainer.classList.add('dragover');
    });
    logoUploadContainer.addEventListener('dragleave', function(e) {
        e.preventDefault();
        logoUploadContainer.classList.remove('dragover');
    });
    logoUploadContainer.addEventListener('drop', function(e) {
        e.preventDefault();
        logoUploadContainer.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) handleLogoFileSelect(files[0]);
    });

    // Handle file select
    function handleLogoFileSelect(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(fileExtension) || !allowedTypes.includes(file.type)) {
            showLogoUploadError('Format file tidak didukung. Hanya JPG, PNG, atau GIF.');
            removeLogoFile();
            return;
        }
        // Max size 2MB
        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            showLogoUploadError('Ukuran file terlalu besar. Maksimal 2MB.');
            removeLogoFile();
            return;
        }
        // Show preview
        logoFileName.textContent = file.name;
        logoFileSize.textContent = formatFileSize(file.size);
        logoFilePreview.classList.add('show');
        hideLogoUploadError();
    }

    // Remove file
    window.removeLogoFile = function() {
        logoInput.value = '';
        logoFilePreview.classList.remove('show');
        hideLogoUploadError();
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function showLogoUploadError(message) {
        logoUploadError.textContent = message;
        logoUploadError.style.display = 'block';
    }
    function hideLogoUploadError() {
        logoUploadError.style.display = 'none';
    }
}); 