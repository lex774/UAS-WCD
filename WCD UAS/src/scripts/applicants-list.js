// Map status ke label
const statusLabelMap = {
    'menunggu': 'Menunggu',
    'direview': 'Direview',
    'terseleksi': 'Terseleksi',
    'interview': 'Interview',
    'lolos': 'Lolos',
    'tidak_terseleksi': 'Tidak Terseleksi',
    'tidak_lolos': 'Tidak Lolos'
};

function showNotif(msg) {
    var notif = document.getElementById('notif');
    notif.innerText = msg;
    notif.style.display = 'block';
    notif.style.background = '#10b981';
    notif.style.color = '#fff';
    notif.style.fontWeight = '600';
    notif.style.fontSize = '1.05rem';
    notif.style.borderRadius = '0.7rem';
    notif.style.boxShadow = '0 2px 8px rgba(16,185,129,0.13)';
    setTimeout(()=>{ notif.style.display = 'none'; }, 2500);
}

function renderStatusActions(card, appId, currentStatus) {
    const status_label_map = {
        'menunggu': 'Menunggu',
        'direview': 'Direview',
        'terseleksi': 'Terseleksi',
        'interview': 'Interview',
        'lolos': 'Lolos',
        'tidak_terseleksi': 'Tidak Terseleksi',
        'tidak_lolos': 'Tidak Lolos'
    };
    const status_icons = {
        'menunggu': '<i class="fa fa-hourglass-half"></i>',
        'direview': '<i class="fa fa-search"></i>',
        'terseleksi': '<i class="fa fa-check-circle"></i>',
        'interview': '<i class="fa fa-comments"></i>',
        'lolos': '<i class="fa fa-trophy"></i>',
        'tidak_terseleksi': '<i class="fa fa-times-circle"></i>',
        'tidak_lolos': '<i class="fa fa-times-circle"></i>'
    };
    const group = card.querySelector('.status-action-group');
    if (!group) return;
    group.innerHTML = '';
    Object.keys(status_label_map).forEach(function(s) {
        if (s !== currentStatus) {
            var btn = document.createElement('button');
            btn.className = 'btn btn-outline btn-sm btn-status-' + s;
            btn.style.fontWeight = '600';
            btn.title = 'Ubah status ke ' + status_label_map[s];
            btn.innerHTML = status_icons[s] + ' ' + status_label_map[s];
            btn.onclick = function() { updateStatus(appId, s); };
            group.appendChild(btn);
        }
    });
}

function updateStatus(appId, status) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'update-application-status.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                showNotif('Status lamaran berhasil diupdate!');
                var card = document.querySelector('.applicant-card[data-app-id="' + appId + '"]');
                if (card) {
                    // Update badge status
                    var badge = card.querySelector('.badge-status');
                    if (badge) {
                        badge.textContent = statusLabelMap[status] || status;
                        badge.className = 'badge badge-status badge-' + status;
                    }
                    // Render ulang tombol aksi status
                    renderStatusActions(card, appId, status);
                    // HAPUS: logika penambahan tombol Lacak Progres
                }
            } else {
                showNotif('Gagal update status: ' + xhr.responseText);
            }
        }
    };
    xhr.send('id=' + appId + '&status=' + status);
}

function showLamaranModalAjax(appId) {
    var modal = document.getElementById('lamaranModal');
    document.getElementById('lamaranContent').innerHTML = '<div class="debug-info">Loading application details for ID: ' + appId + '...</div>';
    modal.classList.add('show');
    fetch('get-application-details.php?id=' + appId)
      .then(res => res.text())
      .then(html => {
        document.getElementById('lamaranContent').innerHTML = html;
      })
      .catch(error => {
        document.getElementById('lamaranContent').innerHTML = '<div class="error-message">Error loading application details: ' + error.message + '</div>';
      });
}

function closeLamaranModal() {
    var modal = document.getElementById('lamaranModal');
    if (modal) {
        modal.classList.remove('show');
        var content = document.getElementById('lamaranContent');
        if (content) content.innerHTML = '<div class="debug-info">Loading application details...</div>';
    }
} 