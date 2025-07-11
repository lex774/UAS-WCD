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
      // Update badge status
      var card = document.querySelector('.progress-card');
      if (card) {
        // Update badge
        var badge = card.querySelector('.badge-status');
        if (badge) {
          badge.className = 'badge badge-status badge-' + newStatus;
          badge.textContent = (newStatus === 'dikerjakan') ? 'Sedang dikerjakan' : (newStatus.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
        }
        // Update tombol
        var actions = card.querySelector('.progress-actions');
        if (actions) {
          if (newStatus === 'dikerjakan') {
            actions.innerHTML = '<button class="btn btn-primary" onclick="updateProgress(' + appId + ', \'sudah_dikerjakan\')">Pekerjaan Selesai</button>';
          } else if (newStatus === 'sudah_dikerjakan') {
            actions.innerHTML = '<span class="badge badge-pembayaran"><i class="fa fa-money-check-alt"></i> Menunggu Pembayaran</span>';
          }
        }
        // Update stepper
        var steps = card.querySelectorAll('.progress-step');
        steps.forEach(function(step) {
          step.classList.remove('active', 'completed');
        });
        if (newStatus === 'dikerjakan') {
          steps[1].classList.add('active');
          steps[0].classList.add('completed');
        } else if (newStatus === 'sudah_dikerjakan') {
          steps[2].classList.add('active');
          steps[0].classList.add('completed');
          steps[1].classList.add('completed');
        }
      }
      // Update sub card di dashboard dan daftar pelamar
      updateSubCard(appId, newStatus);
    } else {
      alert('Gagal update progres: ' + text);
    }
  })
  .catch(err => alert('Error: ' + err));
}

function updateSubCard(appId, newStatus) {
  // Untuk dashboard user pekerja
  var subCard = document.querySelector('.sub-progress-card');
  if (subCard) {
    if (newStatus === 'dikerjakan') {
      subCard.querySelector('.sub-progress-text').innerHTML = '<strong>Pekerjaan sedang berlangsung!</strong><br>Update progress pekerjaan di sini.';
      subCard.classList.remove('sub-progress-green');
      subCard.classList.add('sub-progress-blue');
    } else if (newStatus === 'sudah_dikerjakan') {
      subCard.querySelector('.sub-progress-text').innerHTML = '<strong>Pekerjaan selesai!</strong><br>Menunggu review dan pembayaran.';
      subCard.classList.remove('sub-progress-green', 'sub-progress-blue');
      subCard.classList.add('sub-progress-gray');
    }
  }
  // Untuk daftar pelamar (jika ada)
  var subCardList = document.querySelectorAll('.sub-progress-card[data-app-id="'+appId+'"]');
  subCardList.forEach(function(card) {
    if (newStatus === 'dikerjakan') {
      card.querySelector('.sub-progress-text').innerHTML = '<strong>Pekerjaan sedang berlangsung!</strong><br>Pantau dan lacak progres pekerjaan pelamar ini secara real-time.';
      card.classList.remove('sub-progress-green');
      card.classList.add('sub-progress-blue');
    } else if (newStatus === 'sudah_dikerjakan') {
      card.querySelector('.sub-progress-text').innerHTML = '<strong>Pekerjaan selesai!</strong><br>Menunggu review dan pembayaran.';
      card.classList.remove('sub-progress-green', 'sub-progress-blue');
      card.classList.add('sub-progress-gray');
    }
  });
} 