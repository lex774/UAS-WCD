// Tracking Pekerjaan JS
function openPaymentModal(appId, bankAccount, bankName) {
  console.log('openPaymentModal called', appId, bankAccount, bankName);
  var modal = document.getElementById('paymentModal');
  // Tampilkan nama bank jika ada
  var bankNameElem = document.getElementById('workerBankName');
  if (bankNameElem) {
    bankNameElem.textContent = bankName || '-';
  }
  document.getElementById('workerBankAccount').textContent = bankAccount || '-';
  document.getElementById('paymentAppId').value = appId;
  if (modal) {
    modal.classList.add('show');
    setTimeout(function() {
      if (getComputedStyle(modal).display === 'none' || modal.style.display === 'none') {
        modal.style.display = 'flex';
        modal.style.opacity = 1;
      }
    }, 200);
  } else {
    alert('Modal pembayaran tidak ditemukan di halaman.');
  }
}
function closePaymentModal() {
  document.getElementById('paymentModal').classList.remove('show');
}
function openRatingModal(appId) {
  document.getElementById('ratingModal').classList.add('show');
}
function closeRatingModal() {
  document.getElementById('ratingModal').classList.remove('show');
}

// Function to handle review pekerjaan - hanya mengubah status menjadi reviewed
function reviewPekerjaan(appId) {
  fetch('update-progress-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'app_id=' + encodeURIComponent(appId) + '&new_status=reviewed'
  })
  .then(res => res.text())
  .then(text => {
    if (text.trim() === 'success') {
      showNotif('Pekerjaan berhasil direview!', true);
      setTimeout(() => location.reload(), 1200);
    } else {
      showNotif('Gagal review pekerjaan: ' + text, false);
    }
  })
  .catch(err => showNotif('Error: ' + err, false));
}

// Function to update progress (for worker actions)
function updateProgress(appId, newStatus) {
  fetch('update-progress-api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'app_id=' + encodeURIComponent(appId) + '&new_status=' + encodeURIComponent(newStatus)
  })
  .then(res => res.text())
  .then(text => {
    if (text.trim() === 'success') {
      showNotif('Status berhasil diupdate!', true);
      setTimeout(() => location.reload(), 1200);
    } else {
      showNotif('Gagal update progres: ' + text, false);
    }
  })
  .catch(err => showNotif('Error: ' + err, false));
}

// File upload functionality for payment proof
function setupPaymentFileUpload() {
  const fileContainer = document.getElementById('paymentFileContainer');
  const fileInput = document.getElementById('paymentProofFile');
  const filePreview = document.getElementById('paymentFilePreview');
  const fileName = document.getElementById('paymentFileName');
  const fileSize = document.getElementById('paymentFileSize');

  if (!fileContainer || !fileInput) return;

  // Click to select file
  fileContainer.addEventListener('click', () => {
    fileInput.click();
  });

  // File input change
  fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
      // Validate file size (2MB)
      if (file.size > 2 * 1024 * 1024) {
        alert('File terlalu besar. Maksimal 2MB.');
        fileInput.value = '';
        return;
      }

      // Validate file type
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
      if (!allowedTypes.includes(file.type)) {
        alert('Format file tidak didukung. Gunakan JPG, PNG, atau PDF.');
        fileInput.value = '';
        return;
      }

      // Show file preview
      fileName.textContent = file.name;
      fileSize.textContent = formatFileSize(file.size);
      filePreview.style.display = 'block';
      
      // Update container style
      fileContainer.style.borderColor = '#6366f1';
      fileContainer.style.background = '#f0f9ff';
    }
  });

  // Drag and drop functionality
  fileContainer.addEventListener('dragover', (e) => {
    e.preventDefault();
    fileContainer.style.borderColor = '#6366f1';
    fileContainer.style.background = '#eff6ff';
  });

  fileContainer.addEventListener('dragleave', (e) => {
    e.preventDefault();
    if (!fileContainer.contains(e.relatedTarget)) {
      fileContainer.style.borderColor = '#d1d5db';
      fileContainer.style.background = '#f9fafb';
    }
  });

  fileContainer.addEventListener('drop', (e) => {
    e.preventDefault();
    fileContainer.style.borderColor = '#d1d5db';
    fileContainer.style.background = '#f9fafb';
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
      fileInput.files = files;
      fileInput.dispatchEvent(new Event('change'));
    }
  });
}

// Remove payment file
function removePaymentFile() {
  const fileInput = document.getElementById('paymentProofFile');
  const filePreview = document.getElementById('paymentFilePreview');
  const fileContainer = document.getElementById('paymentFileContainer');
  
  fileInput.value = '';
  filePreview.style.display = 'none';
  fileContainer.style.borderColor = '#d1d5db';
  fileContainer.style.background = '#f9fafb';
}

// Format file size
function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

document.addEventListener('DOMContentLoaded', function() {
  // Setup payment file upload
  setupPaymentFileUpload();
  
  var paymentForm = document.getElementById('paymentProofForm');
  if (paymentForm) {
    paymentForm.onsubmit = async function(e) {
      e.preventDefault();
      const form = e.target;
      const data = new FormData(form);
      const res = await fetch('upload-payment-proof.php', { method: 'POST', body: data });
      const text = await res.text();
      if (text.trim() === 'success') {
        alert('Bukti pembayaran berhasil dikirim!');
        closePaymentModal();
        location.reload();
      } else {
        alert('Gagal upload bukti: ' + text);
      }
    };
  }
  var ratingForm = document.getElementById('ratingForm');
  if (ratingForm) {
    ratingForm.onsubmit = async function(e) {
      e.preventDefault();
      const form = e.target;
      const data = new FormData(form);
      const res = await fetch('submit-review.php', { method: 'POST', body: data });
      const text = await res.text();
      if (text.trim() === 'success') {
        alert('Penilaian berhasil dikirim!');
        closeRatingModal();
        location.reload();
      } else {
        alert('Gagal mengirim penilaian: ' + text);
      }
    };
  }
}); 