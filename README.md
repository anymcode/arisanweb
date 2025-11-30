# 🎲 Sistem Arisan Digital

Aplikasi web fullstack untuk manajemen arisan dengan sistem undian otomatis. Dibuat dengan PHP, MySQL, dan Tailwind CSS dengan tema dark/blackbox yang modern dan menarik.

## ✨ Fitur Utama

### 🎯 Dashboard
- Statistik real-time (total anggota, arisan aktif, pemenang)
- Daftar arisan aktif
- Riwayat pemenang terbaru
- Desain modern dengan glassmorphism effect

### 👥 Manajemen Anggota
- CRUD (Create, Read, Update, Delete) anggota
- Pencarian anggota
- Data lengkap: nama, telepon, alamat
- Status aktif/non-aktif

### 💰 Manajemen Arisan
- Buat periode arisan baru
- Atur nominal dan durasi
- Status arisan (aktif/selesai)
- Grid card layout yang menarik

### 🎰 Sistem Undian
- **Undian otomatis dengan animasi**
- Roda keberuntungan yang berputar
- Efek confetti saat pemenang terpilih
- Konfirmasi pemenang sebelum disimpan
- Hanya anggota yang belum menang yang bisa diundi

### 🏆 Riwayat Pemenang
- Daftar lengkap pemenang
- Statistik total pemenang dan hadiah
- Filter dan pencarian
- Export data (coming soon)

### 💳 Pembayaran
- Catat pembayaran anggota
- Tracking pembayaran per periode
- Status pembayaran (lunas/pending)
- Laporan pembayaran

## 🎨 Desain & UI/UX

- **Tema Dark/Blackbox** yang elegan dan modern
- **Gradient colors** (purple, pink, blue)
- **Glassmorphism effects** dengan backdrop blur
- **Smooth animations** dan micro-interactions
- **Hover effects** yang responsif
- **Custom scrollbar** styling
- **Responsive design** untuk semua device
- **Font Inter** dari Google Fonts

## 🛠️ Teknologi

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Styling**: Tailwind CSS (via CDN)
- **Icons**: Font Awesome 6.4
- **Server**: Apache (XAMPP/LAMPP)

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache Web Server
- XAMPP/LAMPP/WAMP
- Browser modern (Chrome, Firefox, Edge, Safari)

## 🚀 Instalasi

### 1. Clone atau Download Project

```bash
# Jika menggunakan git
git clone [repository-url]

# Atau download dan extract ke folder htdocs
# Lokasi: /opt/lampp/htdocs/arisan
```

### 2. Import Database

```bash
# Masuk ke MySQL
mysql -u root -p

# Atau gunakan phpMyAdmin
# Buka: http://localhost/phpmyadmin
```

Kemudian import file `database/schema.sql`:

**Via Command Line:**
```bash
mysql -u root -p < /opt/lampp/htdocs/arisan/database/schema.sql
```

**Via phpMyAdmin:**
1. Buka phpMyAdmin
2. Klik tab "Import"
3. Pilih file `database/schema.sql`
4. Klik "Go"

### 3. Konfigurasi Database

Edit file `config/database.php` jika perlu:

```php
private $host = "localhost";
private $db_name = "arisan_db";
private $username = "root";
private $password = ""; // Sesuaikan dengan password MySQL Anda
```

### 4. Jalankan Aplikasi

```bash
# Start XAMPP/LAMPP
sudo /opt/lampp/lampp start

# Atau via XAMPP Control Panel
```

Buka browser dan akses:
```
http://localhost/arisan
```

### 5. Login

**Default Credentials:**
- Username: `admin`
- Password: `admin123`

⚠️ **PENTING**: Ubah password default setelah login pertama!

## 📁 Struktur Folder

```
arisan/
├── assets/
│   ├── css/
│   │   └── style.css          # Custom CSS & animations
│   └── js/
│       └── main.js            # JavaScript utilities
├── config/
│   └── database.php           # Database configuration
├── database/
│   └── schema.sql             # Database schema & sample data
├── includes/
│   └── sidebar.php            # Sidebar navigation component
├── index.php                  # Dashboard
├── login.php                  # Login page
├── logout.php                 # Logout handler
├── members.php                # Members management
├── arisan.php                 # Arisan periods management
├── draw.php                   # Lottery/draw system
├── winners.php                # Winners history
├── payments.php               # Payments management
└── README.md                  # This file
```

## 🎮 Cara Penggunaan

### 1. Tambah Anggota
1. Klik menu **"Anggota"**
2. Klik tombol **"Tambah Anggota"**
3. Isi form (nama, telepon, alamat)
4. Klik **"Simpan"**

### 2. Buat Arisan Baru
1. Klik menu **"Kelola Arisan"**
2. Klik tombol **"Buat Arisan Baru"**
3. Isi form:
   - Nama periode (contoh: Arisan Januari 2024)
   - Jumlah per bulan (contoh: 500000)
   - Durasi dalam bulan (contoh: 12)
4. Klik **"Simpan"**

### 3. Lakukan Undian
1. Klik menu **"Undian"**
2. Pilih periode arisan
3. Klik tombol **"MULAI UNDIAN"**
4. Tunggu animasi roda berputar
5. Sistem akan memilih pemenang secara acak
6. Konfirmasi pemenang atau undi ulang
7. Klik **"Konfirmasi"** untuk menyimpan

### 4. Catat Pembayaran
1. Klik menu **"Pembayaran"**
2. Klik tombol **"Catat Pembayaran"**
3. Pilih anggota dan periode arisan
4. Jumlah akan terisi otomatis
5. Pilih tanggal pembayaran
6. Klik **"Simpan"**

### 5. Lihat Pemenang
1. Klik menu **"Pemenang"**
2. Lihat daftar lengkap pemenang
3. Gunakan search untuk mencari
4. Lihat statistik di bagian bawah

## 🎨 Kustomisasi

### Mengubah Warna Tema

Edit file `assets/css/style.css` atau gunakan Tailwind classes:

```css
/* Primary gradient: purple to pink */
from-purple-600 to-pink-600

/* Ganti dengan warna lain, contoh: blue to cyan */
from-blue-600 to-cyan-600
```

### Mengubah Font

Edit di bagian `<head>` setiap file PHP:

```html
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
```

Kemudian ubah di class:
```html
font-['Poppins']
```

## 🔒 Keamanan

- Password di-hash menggunakan `password_hash()` PHP
- Prepared statements untuk mencegah SQL Injection
- Session management untuk autentikasi
- Input validation dan sanitization
- CSRF protection (recommended untuk production)

## 🐛 Troubleshooting

### Database Connection Error
```
Solusi:
1. Pastikan MySQL sudah running
2. Cek username/password di config/database.php
3. Pastikan database 'arisan_db' sudah dibuat
```

### Page Not Found (404)
```
Solusi:
1. Pastikan file ada di /opt/lampp/htdocs/arisan/
2. Cek Apache sudah running
3. Akses: http://localhost/arisan (bukan http://localhost/)
```

### Undian Tidak Berfungsi
```
Solusi:
1. Pastikan ada anggota aktif
2. Pastikan ada arisan aktif
3. Cek console browser untuk error JavaScript
4. Clear browser cache
```

## 📱 Browser Support

- ✅ Chrome (recommended)
- ✅ Firefox
- ✅ Edge
- ✅ Safari
- ⚠️ IE11 (limited support)

## 🚀 Future Enhancements

- [ ] Export data ke Excel/PDF
- [ ] Email notification untuk pemenang
- [ ] WhatsApp integration
- [ ] Multi-admin support
- [ ] Responsive mobile app (PWA)
- [ ] Payment gateway integration
- [ ] Advanced reporting & analytics
- [ ] Backup & restore database
- [ ] Multi-language support

## 📄 License

This project is open source and available under the MIT License.

## 👨‍💻 Developer

Dibuat dengan ❤️ untuk memudahkan pengelolaan arisan tradisional dengan teknologi modern.

## 📞 Support

Jika ada pertanyaan atau masalah:
1. Cek dokumentasi ini terlebih dahulu
2. Lihat troubleshooting section
3. Contact developer

---

**Selamat menggunakan Sistem Arisan Digital! 🎉**
