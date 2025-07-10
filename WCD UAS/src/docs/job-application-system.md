# Sistem Lamaran Pekerjaan - LocalLink

## Overview
Sistem lamaran pekerjaan memungkinkan user untuk melamar pekerjaan yang diposting di platform LocalLink. Sistem ini terdiri dari form lamaran dengan upload CV, tracking status, dan manajemen lamaran.

## Fitur Utama

### 1. Form Lamaran Pekerjaan dengan Upload CV
- **File**: `src/pages/apply-job.php`
- **Fitur**:
  - Upload CV/Resume (PDF, DOC, DOCX, TXT)
  - Form pengisian deskripsi diri
  - Validasi input real-time
  - Auto-save draft
  - Drag & drop file upload
  - Font sans-serif untuk semua input
  - Validasi untuk mencegah user melamar pekerjaan sendiri

### 2. Database Structure
- **File**: `src/db/applications.sql`
- **Tabel**: `applications`
- **Field Utama**:
  - `id`: Primary key
  - `user_id`: ID user yang melamar
  - `job_id`: ID pekerjaan yang dilamar
  - `description`: Deskripsi diri (menggantikan cover letter)
  - `cv_file`: Path file CV yang diupload
  - `status`: Status lamaran (pending/accepted/rejected/withdrawn)
  - `expected_salary`: Gaji yang diharapkan
  - `availability`: Ketersediaan mulai kerja
  - `additional_info`: Informasi tambahan

### 3. Dashboard Integration
- **File**: `user/page/dashboard.php`
- **Fitur**:
  - Menampilkan lamaran yang sudah dikirim
  - Status tracking
  - Preview deskripsi diri
  - Download CV
  - Aksi withdraw lamaran

### 4. API Endpoints
- **Get Application Details**: `src/pages/get-application-details.php`
- **Withdraw Application**: `src/pages/withdraw-application.php`
- **Process Application**: `src/pages/process-application.php`
- **Download CV**: `src/pages/download-cv.php`

## Alur Kerja

### 1. User Melamar Pekerjaan
1. User melihat detail pekerjaan di `job-details.php`
2. Klik tombol "Lamar Pekerjaan Ini"
3. Diarahkan ke `apply-job.php`
4. Upload CV dan isi form lamaran
5. Submit lamaran
6. Diarahkan kembali ke detail pekerjaan dengan pesan sukses

### 2. Validasi Keamanan
1. **Mencegah Self-Application**: User tidak bisa melamar pekerjaan yang mereka posting sendiri
2. **File Upload Security**: Validasi tipe file dan ukuran
3. **Access Control**: Hanya pemilik lamaran atau job poster yang bisa download CV

### 3. Tracking Lamaran
1. User login ke dashboard
2. Klik menu "Pekerjaan Platform"
3. Melihat daftar lamaran yang sudah dikirim
4. Klik "Lihat Lamaran" untuk detail lengkap
5. Dapat menarik lamaran jika masih pending
6. Download CV dari detail lamaran

### 4. Status Lamaran
- **Pending**: Lamaran baru dikirim, menunggu review
- **Accepted**: Lamaran diterima oleh pemberi kerja
- **Rejected**: Lamaran ditolak
- **Withdrawn**: Lamaran ditarik oleh user

## File Structure

```
src/
├── pages/
│   ├── apply-job.php              # Form lamaran dengan upload CV
│   ├── process-application.php    # Handle submission
│   ├── get-application-details.php # API get details
│   ├── withdraw-application.php   # API withdraw
│   └── download-cv.php           # Secure CV download
├── styles/
│   └── apply-job.css             # Styling form dengan font sans-serif
├── scripts/
│   └── apply-job.js              # JavaScript form dengan file upload
└── db/
    └── applications.sql          # Database structure

user/
├── page/
│   └── dashboard.php             # Dashboard dengan tracking
├── style/
│   └── dashboard.css             # Styling dashboard
├── script/
│   └── script-user.js            # JavaScript dashboard
└── uploads/
    └── cv/                       # Directory untuk file CV
```

## Validasi Form

### Required Fields
- CV File (PDF, DOC, DOCX, TXT, max 5MB)
- Description (min 50 karakter, max 2000 karakter)
- Job ID (valid)

### Optional Fields
- Expected Salary (numeric, positive)
- Availability (dropdown)
- Additional Info (max 500 karakter)

### Validation Rules
```javascript
// CV File
if (!cvFile || !cvFile.files[0]) {
    showError('CV harus diupload.');
}

// Description
if (description.length < 50) {
    showError('Deskripsi harus minimal 50 karakter');
}

if (description.length > 2000) {
    showError('Deskripsi tidak boleh lebih dari 2000 karakter');
}

// File Type Validation
const allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
const fileExtension = file.name.split('.').pop().toLowerCase();

if (!allowedExtensions.includes(fileExtension)) {
    showError('Format file tidak didukung');
}

// File Size Validation (5MB)
if (file.size > 5 * 1024 * 1024) {
    showError('Ukuran file terlalu besar. Maksimal 5MB.');
}
```

## Font Styling

### Sans-serif Font Implementation
```css
/* Applied to all form inputs and textareas */
.form-input, .form-textarea, input[type="text"], input[type="email"], 
input[type="number"], textarea, select {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, 
                 "Helvetica Neue", Arial, sans-serif !important;
}
```

## Security Features

### 1. Authentication
- Semua endpoint memerlukan login
- Validasi user ownership untuk aplikasi

### 2. File Upload Security
- Validasi tipe file (PDF, DOC, DOCX, TXT)
- Validasi ukuran file (max 5MB)
- Sanitasi nama file
- Upload ke direktori terpisah

### 3. Self-Application Prevention
```php
// Check if user is the job poster
if ($job['posted_by'] == $user_id) {
    header('Location: job-details.php?id=' . $job_id . '&error=cannot_apply_own_job');
    exit();
}
```

### 4. CV Download Security
- Validasi path file
- Check user permissions
- Secure file serving

## File Upload Features

### 1. Drag & Drop
- Visual feedback saat drag
- Drop zone highlighting
- File validation on drop

### 2. File Preview
- Show selected file name
- Display file size
- Remove file option

### 3. Progress Tracking
- Upload progress indicator
- Error handling
- Success confirmation

## Error Handling

### Common Errors
1. **User not logged in**: Redirect to login page
2. **Already applied**: Show error message
3. **Invalid job ID**: Redirect to job list
4. **Self-application**: Show error message
5. **Invalid file type**: Show validation error
6. **File too large**: Show size limit error
7. **Upload failed**: Show technical error

### Error Messages
```php
// Success
header('Location: job-details.php?id=' . $job_id . '&success=application_submitted');

// Errors
header('Location: apply-job.php?id=' . $job_id . '&error=invalid_file_type');
header('Location: apply-job.php?id=' . $job_id . '&error=file_too_large');
header('Location: job-details.php?id=' . $job_id . '&error=cannot_apply_own_job');
```

## Performance Optimization

### 1. Database Indexes
```sql
CREATE INDEX idx_applications_user_status ON applications(user_id, status);
CREATE INDEX idx_applications_job_status ON applications(job_id, status);
CREATE INDEX idx_applications_applied_at ON applications(applied_at);
```

### 2. File Storage
- Organized directory structure
- Unique file naming
- Automatic cleanup (optional)

### 3. Caching
- Session-based caching untuk user data
- Query optimization untuk dashboard

## Future Enhancements

### 1. Email Notifications
- Notifikasi ke pemberi kerja saat ada lamaran baru
- Notifikasi ke user saat status berubah

### 2. Advanced File Features
- File preview (PDF viewer)
- Multiple file upload
- File compression

### 3. Advanced Features
- Interview scheduling
- Rating system
- Feedback system
- Application analytics

## Testing

### Manual Testing Checklist
- [ ] File upload functionality
- [ ] Drag & drop file upload
- [ ] File type validation
- [ ] File size validation
- [ ] Self-application prevention
- [ ] CV download security
- [ ] Form validation
- [ ] Auto-save functionality
- [ ] Application submission
- [ ] Dashboard display
- [ ] Status updates
- [ ] Withdraw functionality
- [ ] Error handling
- [ ] Mobile responsiveness
- [ ] Font rendering (sans-serif)

### Browser Compatibility
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Deployment Notes

### 1. Database Setup
```sql
-- Run applications.sql to create table
source src/db/applications.sql;
```

### 2. File Permissions
```bash
chmod 644 src/pages/*.php
chmod 644 user/page/*.php
chmod 755 user/uploads/cv/
```

### 3. Configuration
- Update database connection in `src/db/connection.php`
- Set proper file paths
- Configure upload directory permissions
- Set maximum file upload size in PHP config

## Troubleshooting

### Common Issues
1. **File upload fails**: Check directory permissions
2. **CV download fails**: Check file path and permissions
3. **Self-application not blocked**: Check database query
4. **Font not rendering**: Check CSS implementation
5. **File validation fails**: Check allowed file types

### Debug Mode
```php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
``` 