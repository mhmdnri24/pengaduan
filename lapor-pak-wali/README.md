# Lapor Pak Wali 🏛️

Platform Digital Pemerintah Daerah yang terinspirasi dari **Jogja Smart Service (JSS)** untuk melayani aspirasi dan keluhan masyarakat dengan transparansi dan responsivitas tinggi.

## ✨ Fitur Utama

### 🎨 Desain Terinspirasi JSS Jogja
- **Government Style**: Desain yang terinspirasi dari portal pemerintah JSS Jogjakota
- **Professional Layout**: Layout yang clean dan profesional sesuai standar pemerintahan
- **Red-Blue Theme**: Kombinasi warna merah-biru yang mencerminkan identitas pemerintahan
- **Google Images**: Menggunakan gambar berkualitas tinggi dari Unsplash untuk placeholder

### 🏛️ Identitas Visual Government
- **Government Building Backgrounds**: Background dengan gambar gedung pemerintahan
- **Official Color Scheme**: Skema warna resmi dengan gradien merah-biru
- **Professional Icons**: Ikon yang sesuai dengan layanan pemerintah
- **Card-based Layout**: Layout berbasis kartu seperti JSS dengan hover effects

### 🔐 Sistem Registrasi & Login
- **Registrasi Mudah**: Daftar hanya dengan nama, NIK KTP 16 digit, dan nomor WhatsApp aktif
- **Multi Login**: Login menggunakan NIK atau nomor WhatsApp
- **OTP Verification**: Sistem verifikasi OTP melalui WhatsApp
- **Session Management**: Sesi login dengan "Remember Me" dan auto-logout

### 📱 Progressive Web App (PWA)
- **Installable**: Dapat diinstall sebagai aplikasi di smartphone
- **Offline Support**: Bekerja tanpa koneksi internet
- **Push Notifications**: Notifikasi real-time untuk update laporan
- **Background Sync**: Sinkronisasi otomatis saat koneksi kembali

### 🎨 Desain Modern & Responsif
- **Mobile First**: Dioptimalkan untuk smartphone
- **Gradient Biru**: Tema warna biru gradient yang menarik
- **Responsive Design**: Tampil sempurna di semua ukuran layar
- **Modern UI/UX**: Interface yang intuitif dan user-friendly

### 🚀 Performa & Keamanan
- **Fast Loading**: Optimasi performa untuk loading cepat
- **Data Security**: Perlindungan data dengan enkripsi
- **Form Validation**: Validasi real-time untuk semua input
- **Error Handling**: Penanganan error yang baik

## 📁 Struktur Aplikasi

```
lapor-pak-wali/
├── index.html              # Halaman utama/landing
├── manifest.json           # PWA manifest
├── sw.js                   # Service worker
├── test-validation.html    # Halaman testing
│
├── css/
│   └── style.css          # Stylesheet utama
│
├── js/
│   ├── app.js             # JavaScript utama
│   ├── login.js           # Logic login
│   ├── register.js        # Logic registrasi
│   └── dashboard.js       # Logic dashboard
│
├── pages/
│   ├── login.html         # Halaman login
│   ├── register.html      # Halaman registrasi
│   └── dashboard.html     # Dashboard pengguna
│
└── images/
    └── icon-generator.html # Generator ikon PWA
```

## 🚀 Cara Menjalankan

### 1. Server Lokal
```bash
cd lapor-pak-wali
python3 -m http.server 8080
```

### 2. Akses Aplikasi
Buka browser dan kunjungi: `http://localhost:8080`

### 3. Testing
Akses halaman validasi: `http://localhost:8080/test-validation.html`

## 📱 Fitur PWA

### Install di Android
1. Buka aplikasi di Chrome
2. Tap menu "Add to Home Screen"
3. Aplikasi akan terinstall di home screen

### Install di iOS
1. Buka aplikasi di Safari
2. Tap tombol "Share"
3. Pilih "Add to Home Screen"

### Install di Desktop
1. Buka aplikasi di Chrome/Edge
2. Klik ikon install di address bar
3. Klik "Install" untuk menginstall sebagai aplikasi desktop

## 🎯 User Journey

### 1. Registrasi Pengguna Baru
```
Landing Page → Daftar → Form Registrasi → Verifikasi → Dashboard
```

### 2. Login Pengguna Existing
```
Landing Page → Masuk → Form Login → Dashboard
```

### 3. Membuat Laporan
```
Dashboard → Buat Laporan → Form Laporan → Submit → Konfirmasi
```

## 🔧 Teknologi yang Digunakan

### Frontend
- **HTML5**: Struktur aplikasi modern
- **CSS3**: Styling dengan Grid, Flexbox, dan Custom Properties
- **JavaScript ES6+**: Interaktivitas dan logic aplikasi
- **Font Awesome**: Icon library
- **Google Fonts**: Typography (Inter font)

### PWA Technologies
- **Service Worker**: Offline functionality
- **Web App Manifest**: App metadata
- **Cache API**: Resource caching
- **Push API**: Notifications (ready)
- **Background Sync**: Data synchronization (ready)

### Storage
- **localStorage**: User data dan session
- **sessionStorage**: Temporary data (OTP)
- **Cache API**: Static assets

## 📋 Validasi Form

### Nama Lengkap
- ✅ Minimal 3 karakter
- ✅ Maksimal 100 karakter
- ✅ Hanya huruf, spasi, titik, tanda kutip, dan tanda hubung

### NIK KTP
- ✅ Harus 16 digit angka
- ✅ Tidak boleh duplikat
- ✅ Validasi format NIK Indonesia

### Nomor WhatsApp
- ✅ 10-13 digit angka
- ✅ Tanpa angka 0 di depan
- ✅ Format internasional (+62)
- ✅ Tidak boleh duplikat

## 🎨 Desain System

### Inspirasi Desain
Aplikasi ini terinspirasi dari **Jogja Smart Service (JSS)** - platform digital Pemerintah Kota Yogyakarta:
- **URL Referensi**: https://jss.jogjakota.go.id/
- **Filosofi**: Portal pemerintah yang modern, profesional, dan user-friendly
- **Karakteristik**: Layout berbasis kartu, warna government, dan UX yang intuitif

### Visual Elements
- **Background Images**: High-quality government building images from Unsplash
- **Card Design**: JSS-style cards dengan gradient borders dan hover effects
- **Icons**: Professional government service icons
- **Typography**: Clean dan readable dengan hierarki yang jelas

### Government Service Categories
- 📢 **Pengaduan & Keluhan**: Laporan masyarakat umum
- 😧 **Infrastruktur**: Jalan, jembatan, fasilitas umum
- 🏥 **Kesehatan**: Layanan kesehatan masyarakat
- 🎓 **Pendidikan**: Fasilitas dan layanan pendidikan
- 🌿 **Lingkungan**: Kebersihan dan pengelolaan sampah
- ⚖️ **Hukum & Ketertiban**: Keamanan dan penegakan hukum

### Color Palette (JSS Inspired)
```css
/* Primary Government Colors */
--primary-red: #dc2626     /* Government Red */
--primary-blue: #1d4ed8    /* Government Blue */
--secondary-blue: #3b82f6  /* Secondary Blue */
--accent-gold: #f59e0b     /* Accent Gold */

/* Gradients */
--gradient-hero: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #1d4ed8 100%)
--gradient-primary: linear-gradient(135deg, #dc2626 0%, #1d4ed8 50%, #3b82f6 100%)
--gradient-secondary: linear-gradient(135deg, #991b1b 0%, #1e40af 100%)
```

### Breakpoints
```css
Mobile: < 768px
Tablet: 768px - 1024px
Desktop: > 1024px
```

### Typography
- **Font Family**: Inter (Google Fonts)
- **Font Weights**: 300, 400, 500, 600, 700

## 🔍 Testing

### Manual Testing
1. Akses `test-validation.html`
2. Periksa semua komponen
3. Test responsivitas di berbagai ukuran layar
4. Test fungsionalitas form

### Browser Testing
- ✅ Chrome (Desktop & Mobile)
- ✅ Firefox (Desktop & Mobile)
- ✅ Safari (Desktop & Mobile)
- ✅ Edge (Desktop)

### Device Testing
- ✅ iPhone (Portrait & Landscape)
- ✅ Android (Portrait & Landscape)
- ✅ Tablet (Portrait & Landscape)
- ✅ Desktop (Various resolutions)

## 🚀 Deployment

### Production Ready
- ✅ Optimized CSS dan JavaScript
- ✅ Compressed images (ketika ditambahkan)
- ✅ Service Worker untuk caching
- ✅ Proper error handling
- ✅ Security headers (perlu konfigurasi server)

### Hosting Options
1. **Static Hosting**: Netlify, Vercel, GitHub Pages
2. **Traditional Hosting**: Apache, Nginx
3. **CDN**: Cloudflare, AWS CloudFront

## 📈 Performance

### Lighthouse Score (Target)
- **Performance**: 95+
- **Accessibility**: 95+
- **Best Practices**: 95+
- **SEO**: 95+
- **PWA**: 100

### Optimization
- ✅ Minimal HTTP requests
- ✅ Compressed assets
- ✅ Efficient caching strategy
- ✅ Lazy loading (siap implementasi)

## 🔒 Security

### Data Protection
- ✅ Input validation dan sanitization
- ✅ XSS protection
- ✅ CSRF protection (client-side)
- ✅ Secure data storage

### Privacy
- ✅ Minimal data collection
- ✅ Local data storage
- ✅ No tracking scripts
- ✅ GDPR compliant design

## 🌟 Future Enhancements

### Fitur Tambahan
- [ ] Geolocation untuk lokasi otomatis
- [ ] Camera integration untuk foto laporan
- [ ] Real-time chat dengan admin
- [ ] Push notifications untuk update status
- [ ] Dark mode theme
- [ ] Multi-language support

### Technical Improvements
- [ ] IndexedDB untuk data kompleks
- [ ] Web Workers untuk background tasks
- [ ] WebRTC untuk video calls
- [ ] Payment integration (jika diperlukan)

## 📞 Support

### Kontak
- **WhatsApp**: +62 812-3456-7890
- **Email**: info@laporpakwali.id
- **Website**: https://laporpakwali.id

### Jam Operasional
- **Senin - Jumat**: 08:00 - 17:00 WIB
- **Sabtu - Minggu**: 09:00 - 15:00 WIB

## 📄 License

Copyright © 2024 Lapor Pak Wali. All rights reserved.

---

**Dibuat dengan ❤️ untuk memudahkan komunikasi antara masyarakat dan pemerintah daerah.**