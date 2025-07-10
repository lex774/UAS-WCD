// Apply Job JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // File upload functionality
    const fileInput = document.getElementById('cv_file');
    const fileUploadContainer = document.getElementById('fileUploadContainer');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const uploadError = document.getElementById('uploadError');
    const uploadSuccess = document.getElementById('uploadSuccess');

    // Character counting
    const descriptionTextarea = document.getElementById('description');
    const charCount = document.getElementById('char-count');
    const additionalTextarea = document.getElementById('additional_info');
    const additionalCharCount = document.getElementById('additional-char-count');

    // File upload container click handler
    fileUploadContainer.addEventListener('click', function() {
        fileInput.click();
    });

    // File input change handler
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleFileSelect(file);
        }
    });

    // Drag and drop functionality
    fileUploadContainer.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileUploadContainer.classList.add('dragover');
    });

    fileUploadContainer.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileUploadContainer.classList.remove('dragover');
    });

    fileUploadContainer.addEventListener('drop', function(e) {
        e.preventDefault();
        fileUploadContainer.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });

    // Handle file selection
    function handleFileSelect(file) {
        // Validate file type
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
        const allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedExtensions.includes(fileExtension)) {
            showUploadError('Format file tidak didukung. Gunakan PDF, DOC, DOCX, atau TXT.');
            return;
        }

        // Validate file size (5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if (file.size > maxSize) {
            showUploadError('Ukuran file terlalu besar. Maksimal 5MB.');
            return;
        }

        // Display file info
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        filePreview.classList.add('show');
        
        // Hide error and show success
        hideUploadError();
        showUploadSuccess('File berhasil dipilih');
    }

    // Remove file function
    window.removeFile = function() {
        fileInput.value = '';
        filePreview.classList.remove('show');
        hideUploadError();
        hideUploadSuccess();
    }

    // Character counting for description
    if (descriptionTextarea) {
        descriptionTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length;
            
            if (length > 1800) {
                charCount.style.color = '#dc2626';
            } else if (length > 1500) {
                charCount.style.color = '#f59e0b';
            } else {
                charCount.style.color = '#6b7280';
            }
        });
    }

    // Character counting for additional info
    if (additionalTextarea) {
        additionalTextarea.addEventListener('input', function() {
            const length = this.value.length;
            additionalCharCount.textContent = length;
            
            if (length > 400) {
                additionalCharCount.style.color = '#dc2626';
            } else if (length > 300) {
                additionalCharCount.style.color = '#f59e0b';
            } else {
                additionalCharCount.style.color = '#6b7280';
            }
        });
    }

    // Form validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const description = descriptionTextarea.value.trim();
            const file = fileInput.files[0];
            
            let hasError = false;
            
            // Clear previous errors
            clearFormErrors();
            
            // Validate description
            if (!description) {
                showFieldError(descriptionTextarea, 'Deskripsi harus diisi');
                hasError = true;
            } else if (description.length < 50) {
                showFieldError(descriptionTextarea, 'Deskripsi minimal 50 karakter');
                hasError = true;
            }
            
            // Validate file
            if (!file) {
                showUploadError('CV harus diupload');
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
            }
        });
    }

    // Utility functions
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function showUploadError(message) {
        uploadError.textContent = message;
        uploadError.style.display = 'block';
    }

    function hideUploadError() {
        uploadError.style.display = 'none';
    }

    function showUploadSuccess(message) {
        uploadSuccess.textContent = message;
        uploadSuccess.style.display = 'block';
    }

    function hideUploadSuccess() {
        uploadSuccess.style.display = 'none';
    }

    function showFieldError(field, message) {
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-text';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    function clearFormErrors() {
        const errorTexts = document.querySelectorAll('.error-text');
        errorTexts.forEach(error => error.remove());
        
        const errorFields = document.querySelectorAll('.form-input.error');
        errorFields.forEach(field => field.classList.remove('error'));
    }
}); 