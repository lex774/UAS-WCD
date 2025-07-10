function toggleContent(targetId) {
    // Tutup semua konten yang ada
    const allContents = document.querySelectorAll('.card-content');
    allContents.forEach(card => {
        card.classList.add('hidden');
        card.classList.remove('show');
    });

    // Remove active class from all stat items
    const allStatItems = document.querySelectorAll('.stat-item');
    allStatItems.forEach(item => {
        item.classList.remove('active');
    });

    // Add active class to clicked item
    const clickedItem = document.querySelector(`[data-target="${targetId}"]`);
    if (clickedItem) {
        clickedItem.classList.add('active');
    }

    // Temukan konten yang akan ditampilkan
    const content = document.getElementById(targetId);

    // Jika konten ditemukan, tampilkan
    if (content) {
        content.classList.remove('hidden');
        content.classList.add('show');
    }
}

// Function untuk menampilkan modal avatar
function showAvatarModal(imgUrl) {
    var modal = document.getElementById('avatarModal');
    var modalImg = document.getElementById('avatarModalImg');
    modal.style.display = 'flex';
    modalImg.src = imgUrl;
    document.body.style.overflow = 'hidden';
}

// Function untuk update status pekerjaan via AJAX
function updateJobStatus(jobId, newStatus) {
    const button = event.target;
    const jobCard = button.closest('.job-card');
    const badge = jobCard.querySelector('.badge-status');
    
    // Tambahkan loading state
    button.classList.add('loading');
    button.disabled = true;
    
    // Kirim AJAX request
    fetch('update-job-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            job_id: jobId,
            new_status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update badge
            if (data.new_status === 'TERBUKA') {
                badge.className = 'badge badge-status badge-open';
                badge.textContent = 'Terbuka';
            } else {
                badge.className = 'badge badge-status badge-closed';
                badge.textContent = 'Tertutup';
            }
            
            // Update button
            if (data.new_status === 'TERBUKA') {
                button.className = 'btn btn-status btn-status-close';
                button.textContent = 'Tutup Lowongan';
                button.onclick = function() {
                    updateJobStatus(jobId, 'TERTUTUP');
                };
            } else {
                button.className = 'btn btn-status btn-status-open';
                button.textContent = 'Buka Lowongan';
                button.onclick = function() {
                    updateJobStatus(jobId, 'TERBUKA');
                };
            }
            
            // Tampilkan notifikasi sukses (opsional)
            showNotification('Status berhasil diperbarui!', 'success');
        } else {
            // Tampilkan error
            showNotification('Gagal memperbarui status: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat memperbarui status', 'error');
    })
    .finally(() => {
        // Hapus loading state
        button.classList.remove('loading');
        button.disabled = false;
    });
}

// Function untuk menampilkan notifikasi
function showNotification(message, type) {
    // Buat elemen notifikasi
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 0.5rem;
        color: white;
        font-weight: 500;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        max-width: 300px;
    `;
    
    if (type === 'success') {
        notification.style.backgroundColor = '#10b981';
    } else {
        notification.style.backgroundColor = '#ef4444';
    }
    
    notification.textContent = message;
    
    // Tambahkan CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Tambahkan ke body
    document.body.appendChild(notification);
    
    // Hapus setelah 3 detik
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Event Listener untuk setiap elemen statistik
document.addEventListener('DOMContentLoaded', function() {
    const statItems = document.querySelectorAll('.stat-item');
    
    statItems.forEach(item => {
        item.addEventListener('click', () => {
            const targetId = item.getAttribute('data-target');
            toggleContent(targetId);
        });
    });

    // Set default active state (Pekerjaan Diposting)
    const defaultStatItem = document.querySelector('[data-target="jobs-posted"]');
    if (defaultStatItem) {
        defaultStatItem.classList.add('active');
        // Show default content
        const defaultContent = document.getElementById('jobs-posted');
        if (defaultContent) {
            defaultContent.classList.remove('hidden');
            defaultContent.classList.add('show');
        }
    }

    // Profile dropdown functionality
    var avatar = document.getElementById('profileAvatar');
    var dropdown = document.getElementById('profileDropdown');
    if (avatar && dropdown) {
        avatar.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }

    // Avatar modal functionality
    var closeAvatarModal = document.getElementById('closeAvatarModal');
    var avatarModal = document.getElementById('avatarModal');
    
    if (closeAvatarModal) {
        closeAvatarModal.onclick = function() {
            document.getElementById('avatarModal').style.display = 'none';
            document.body.style.overflow = '';
        };
    }
    
    if (avatarModal) {
        avatarModal.onclick = function(e) {
            if (e.target === this) {
                this.style.display = 'none';
                document.body.style.overflow = '';
            }
        };
    }
});

//Script settings account 
// Smooth scrolling for sidebar links
document.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', function(event) {
      event.preventDefault();
      const targetId = this.getAttribute('href');
      const targetElement = document.querySelector(targetId);
  
      if (targetElement) {
        targetElement.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });
  
  // Sign Out Confirmation
  document.querySelector('.sign-out-button').addEventListener('click', function() {
    if (confirm('Are you sure you want to sign out?')) {
      // Add your sign-out logic here (e.g., redirect to login page)
      alert('You have signed out successfully!');
    }
  });

// Application Details Modal
function viewApplicationDetails(applicationId) {
    // Create modal HTML
    const modalHTML = `
        <div id="applicationModal" class="application-modal">
            <div class="application-modal-content">
                <div class="application-modal-header">
                    <h2 class="application-modal-title">Detail Lamaran</h2>
                    <button class="application-modal-close" onclick="closeApplicationModal()">&times;</button>
                </div>
                <div class="application-details">
                    <div class="application-section">
                        <h3><i class="fa fa-spinner fa-spin"></i> Memuat data...</h3>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Show modal
    document.getElementById('applicationModal').style.display = 'block';
    
    // Load application details via AJAX
    loadApplicationDetails(applicationId);
}

function closeApplicationModal() {
    const modal = document.getElementById('applicationModal');
    if (modal) {
        modal.remove();
    }
}

function loadApplicationDetails(applicationId) {
    // Create AJAX request to get application details
    fetch(`../../src/pages/get-application-details.php?id=${applicationId}&as=json`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayApplicationDetails(data.application);
            } else {
                displayError('Gagal memuat detail lamaran');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayError('Terjadi kesalahan saat memuat data');
        });
}

function displayApplicationDetails(application) {
    const detailsContainer = document.querySelector('.application-details');
    
    const detailsHTML = `
        <div class="application-section">
            <h3><i class="fa fa-briefcase"></i> Informasi Pekerjaan</h3>
            <div class="application-info">
                <div class="application-info-item">
                    <i class="fa fa-tag"></i>
                    <span><strong>Posisi:</strong> ${application.job_title}</span>
                </div>
                <div class="application-info-item">
                    <i class="fa fa-building"></i>
                    <span><strong>Perusahaan:</strong> ${application.company_name}</span>
                </div>
                <div class="application-info-item">
                    <i class="fa fa-calendar"></i>
                    <span><strong>Tanggal Lamaran:</strong> ${application.applied_date}</span>
                </div>
                <div class="application-info-item">
                    <i class="fa fa-clock"></i>
                    <span><strong>Status:</strong> 
                        <span class="badge badge-${getStatusBadgeClass(application.status)}">
                            ${getStatusText(application.status)}
                        </span>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="application-section">
            <h3><i class="fa fa-file-alt"></i> CV/Resume</h3>
            <div class="application-content">
                <div class="cv-file-info">
                    <i class="fa fa-file-pdf"></i>
                    <span><strong>File:</strong> ${application.cv_filename}</span>
                    <a href="../../src/pages/download-cv.php?file=${encodeURIComponent(application.cv_file)}" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fa fa-download"></i> Download CV
                    </a>
                </div>
            </div>
        </div>
        
        <div class="application-section">
            <h3><i class="fa fa-edit"></i> Deskripsi Diri</h3>
            <div class="application-content">
                <p>${application.cover_letter}</p>
            </div>
        </div>
        
        ${application.expected_salary ? `
        <div class="application-section">
            <h3><i class="fa fa-money-bill-wave"></i> Informasi Tambahan</h3>
            <div class="application-info">
                <div class="application-info-item">
                    <i class="fa fa-dollar-sign"></i>
                    <span><strong>Gaji yang Diharapkan:</strong> ${formatCurrency(application.expected_salary)}</span>
                </div>
                ${application.availability ? `
                <div class="application-info-item">
                    <i class="fa fa-calendar-alt"></i>
                    <span><strong>Ketersediaan:</strong> ${getAvailabilityText(application.availability)}</span>
                </div>
                ` : ''}
            </div>
            ${application.additional_info ? `
            <div class="application-content">
                <h4>Informasi Tambahan:</h4>
                <p>${application.additional_info}</p>
            </div>
            ` : ''}
        </div>
        ` : ''}
        
        <div class="application-section">
            <h3><i class="fa fa-cog"></i> Aksi</h3>
            <div class="application-actions">
                <a href="../../src/pages/job-details.php?id=${application.job_id}" class="btn btn-primary btn-sm">
                    <i class="fa fa-eye"></i> Lihat Detail Pekerjaan
                </a>
                <button class="btn btn-primary btn-sm" onclick="withdrawApplication(${application.id})">
                    <i class="fa fa-times"></i> Tarik Lamaran
                </button>
            </div>
        </div>
    `;
    
    detailsContainer.innerHTML = detailsHTML;
}

function getStatusBadgeClass(status) {
    switch(status) {
        case 'accepted': return 'success';
        case 'rejected': return 'danger';
        case 'pending': return 'warning';
        default: return 'secondary';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'accepted': return 'Diterima';
        case 'rejected': return 'Ditolak';
        case 'pending': return 'Menunggu';
        default: return status.toUpperCase();
    }
}

function getAvailabilityText(availability) {
    switch(availability) {
        case 'immediate': return 'Segera';
        case '1_week': return '1 minggu';
        case '2_weeks': return '2 minggu';
        case '1_month': return '1 bulan';
        case 'negotiable': return 'Dapat dinegosiasikan';
        default: return availability;
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

function displayError(message) {
    const detailsContainer = document.querySelector('.application-details');
    detailsContainer.innerHTML = `
        <div class="application-section">
            <h3><i class="fa fa-exclamation-triangle"></i> Error</h3>
            <p style="color: #dc2626;">${message}</p>
        </div>
    `;
}

function withdrawApplication(applicationId) {
    if (confirm('Apakah Anda yakin ingin menarik lamaran ini?')) {
        fetch(`../../src/pages/withdraw-application.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                application_id: applicationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Lamaran berhasil ditarik');
                closeApplicationModal();
                location.reload(); // Refresh page to update list
            } else {
                alert('Gagal menarik lamaran: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menarik lamaran');
        });
    }
}

function deleteJob(jobId, btn) {
    if (!confirm('Apakah Anda yakin ingin menghapus lowongan ini? Semua lamaran terkait juga akan dihapus.')) return;
    btn.disabled = true;
    btn.textContent = 'Menghapus...';
    fetch('../../src/pages/delete-job.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ job_id: jobId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Hapus card dari DOM
            const card = btn.closest('.job-card');
            if (card) card.remove();
            showNotification('Lowongan berhasil dihapus!', 'success');
        } else {
            showNotification('Gagal menghapus: ' + (data.message || 'Unknown error'), 'error');
            btn.disabled = false;
            btn.textContent = 'Hapus';
        }
    })
    .catch(() => {
        showNotification('Terjadi kesalahan saat menghapus', 'error');
        btn.disabled = false;
        btn.textContent = 'Hapus';
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('applicationModal');
    if (modal && event.target === modal) {
        closeApplicationModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeApplicationModal();
    }
});

// POLLING STATUS LAMARAN REAL-TIME DI DASHBOARD
if (window.location.pathname.includes('dashboard.php')) {
    setInterval(function() {
        fetch('../../src/pages/get-applications-status.php')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.applications) {
                    document.querySelectorAll('.job-card').forEach(function(card) {
                        var appId = card.getAttribute('data-app-id');
                        if (appId && data.applications[appId]) {
                            var badge = card.querySelector('.badge');
                            if (badge && badge.textContent.toLowerCase() !== getStatusText(data.applications[appId]).toLowerCase()) {
                                badge.textContent = getStatusText(data.applications[appId]);
                                badge.className = 'badge badge-' + getStatusBadgeClass(data.applications[appId]);
                            }
                        }
                    });
                }
            });
    }, 5000); // polling setiap 5 detik
}