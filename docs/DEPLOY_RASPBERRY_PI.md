# Deploy RVM ke Raspberry Pi 4 (mesin kiosk mandiri)

Panduan ini mengasumsikan Raspberry Pi 4 kamu **sudah ada OS-nya dan bisa diakses lewat SSH**. Semua perintah di bawah dijalankan **di Pi** (lewat SSH), bukan di laptop.

Ganti `pi` / `/home/pi/RVM` di seluruh contoh kalau username atau lokasi clone kamu beda. File-file config siap pakai ada di folder [`deploy/`](../deploy/) di root repo ini — panduan ini menjelaskan cara pakainya.

---

## 0. Prasyarat

```bash
uname -m
```
Harus keluar **`aarch64`** (64-bit). Kalau keluar `armv7l` (32-bit), PyTorch/ultralytics tidak punya wheel resmi — kamu perlu flash ulang SD card pakai **Raspberry Pi OS (64-bit)** dulu sebelum lanjut.

Cek kamera sudah aktif:
```bash
libcamera-hello --list-cameras
```
Kalau kamera tidak terdeteksi, aktifkan dulu lewat `sudo raspi-config` → Interface Options → Camera, lalu reboot.

Panduan ini juga mengasumsikan Pi jalan **Raspberry Pi OS Desktop** (bukan Lite) — dibutuhkan untuk mode kiosk (Chromium fullscreen) di langkah 8.

---

## 1. Install paket sistem

```bash
sudo apt update && sudo apt full-upgrade -y

sudo apt install -y \
  nginx mariadb-server \
  php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath \
  composer \
  python3-venv python3-pip python3-picamera2 \
  pigpio \
  chromium-browser \
  git

# Node.js 18+ (Raspberry Pi OS apt biasanya versi lama, pakai NodeSource)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# pigpio daemon harus jalan permanen (dipakai servo lewat gpiozero)
sudo systemctl enable --now pigpiod
```

> Cek versi PHP yang ke-install (`php -v`) — kalau bukan 8.2, sesuaikan nama paket di atas dan socket path di [`deploy/nginx-rvm.conf`](../deploy/nginx-rvm.conf) & [`deploy/rvm-ai.service`](../deploy/rvm-ai.service).

---

## 2. Permission GPIO & kamera

```bash
sudo usermod -aG gpio,video pi
```
Logout/login ulang (atau reboot) supaya grup baru kepakai.

---

## 3. Clone project & setup database

```bash
cd ~
git clone https://github.com/Lutpii/RVM.git
cd RVM

sudo mysql -u root <<'SQL'
CREATE DATABASE rvm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rvm_user'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_INI';
GRANT ALL PRIVILEGES ON rvm_db.* TO 'rvm_user'@'localhost';
FLUSH PRIVILEGES;
SQL

mysql -u rvm_user -p rvm_db < database/rvm_db.sql
```

`database/rvm_db.sql` sekarang adalah export segar dari database dev (bukan dump lama yang basi) — sudah termasuk skema terbaru (kolom `kiosk_token`, `ai_confidence`) **dan** tabel `migrations` itu sendiri, jadi `php artisan migrate` nanti tidak akan bentrok "table already exists" kalau dijalankan lagi.

---

## 4. Backend (Laravel)

```bash
cd ~/RVM/BackEnd
composer install --no-dev --optimize-autoloader

cp ../deploy/env.production.example .env
nano .env   # isi semua <PLACEHOLDER> — lihat catatan di dalam file-nya

php artisan key:generate
php artisan storage:link
# php artisan migrate   -- opsional, cuma buat jaga-jaga; harusnya no-op karena
#                           semua migration sudah tercatat lewat import di atas

sudo chown -R pi:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Login**: karena database yang diimport adalah data dev asli (bukan seed generik), akun-akun yang sudah ada dari testing sebelumnya ikut terbawa — termasuk akun admin yang sudah kamu pakai selama ini. Kalau mau bikin akun admin baru, pakai cara yang sama seperti sebelumnya:
```bash
mysql -u rvm_user -p rvm_db -e "UPDATE users SET role='admin', is_verified=1 WHERE email='emailkamu@example.com';"
```

---

## 5. AI service (kamera + YOLO + servo)

```bash
cd ~/RVM/BackEnd/ai_service

# --system-site-packages WAJIB — supaya picamera2 (dari apt) ikut terbaca di venv
python3 -m venv venv --system-site-packages
source venv/bin/activate
pip install -r requirements.txt
pip install gpiozero
deactivate
```

Taruh file model (`best.pt` atau `best_exp6.pt`) di `~/RVM/best.pt` (root project) atau `~/RVM/BackEnd/ai_service/model/best.pt` — `app.py` otomatis mencari di kedua lokasi itu.

```bash
sudo cp ~/RVM/deploy/rvm-ai.service /etc/systemd/system/rvm-ai.service
sudo systemctl daemon-reload
sudo systemctl enable --now rvm-ai
```

**Verifikasi wajib sebelum lanjut:**
```bash
curl http://127.0.0.1:5000/health
```
Harus muncul `"hardware_available": true`. Kalau masih `false`:
```bash
journalctl -u rvm-ai -n 50 --no-pager
```
Penyebab umum: belum logout/login setelah `usermod` (grup gpio/video belum aktif), `pigpiod` belum jalan (`systemctl status pigpiod`), atau kabel servo belum tersambung ke GPIO17 (pan) / GPIO27 (tilt).

---

## 6. Frontend (build production)

```bash
cd ~/RVM/FrontEnd
npm install
npm run build
```
Hasil build ada di `FrontEnd/dist/` — tidak perlu ubah apa pun di kode-nya, `api.js` sudah default ke `/api` relatif (same-origin lewat Nginx nanti).

---

## 7. Nginx + HTTPS

Generate sertifikat self-signed (untuk kiosk LAN internal — lihat catatan upgrade ke cert asli di langkah 9):
```bash
sudo mkdir -p /etc/ssl/rvm
sudo openssl req -x509 -nodes -days 3650 \
  -newkey rsa:2048 \
  -keyout /etc/ssl/rvm/rvm-selfsigned.key \
  -out /etc/ssl/rvm/rvm-selfsigned.crt \
  -subj "/CN=rvm-kiosk"
```

Pasang site config:
```bash
sudo cp ~/RVM/deploy/nginx-rvm.conf /etc/nginx/sites-available/rvm
sudo ln -s /etc/nginx/sites-available/rvm /etc/nginx/sites-enabled/rvm
sudo rm -f /etc/nginx/sites-enabled/default

sudo nginx -t && sudo systemctl reload nginx
```

> Kalau path clone kamu bukan `/home/pi/RVM`, edit dulu path di [`deploy/nginx-rvm.conf`](../deploy/nginx-rvm.conf) & [`deploy/rvm-ai.service`](../deploy/rvm-ai.service) sebelum copy.

---

## 8. Mode kiosk (fullscreen di layar Pi)

```bash
mkdir -p ~/.config/autostart
cp ~/RVM/deploy/rvm-kiosk-autostart.desktop ~/.config/autostart/
```
Ganti `RVM-001` di file itu dengan `machine_code` mesin fisik ini kalau berbeda.

Matikan screen blanking supaya layar kiosk tidak tidur — tambahkan ke `~/.config/lxsession/LXDE-pi/autostart`:
```
@xset s off
@xset -dpms
@xset s noblank
```

---

## 9. Reboot & verifikasi end-to-end

```bash
sudo reboot
```

Setelah nyala lagi:
```bash
systemctl status nginx mariadb php8.2-fpm pigpiod rvm-ai
```
Semua harus `active (running)`.

Dari HP di jaringan yang sama, buka `https://<ip-pi>` (klik "Advanced → Proceed" untuk sertifikat self-signed), lalu jalankan alur penuh: scan QR di layar kiosk → login → mulai sesi → capture → classify → complete — dan pastikan **servo benar-benar bergerak** menyortir item ke bin yang sesuai.

---

## 10. Opsional — HTTPS dengan sertifikat terpercaya (Let's Encrypt)

Ini **tidak wajib** untuk kiosk yang cuma dipakai di LAN internal. Perlu kalau Google Login harus mulus dari HP pengunjung publik tanpa klik "Advanced":

1. Daftar subdomain gratis di [duckdns.org](https://www.duckdns.org), arahkan ke IP publik jaringan kamu.
2. Port-forward router: port 443 (dan 80 untuk verifikasi) ke IP lokal Pi. (Ini setting di router, bukan sesuatu yang bisa dibantu dari sini.)
3. Install & jalankan certbot:
   ```bash
   sudo apt install -y certbot python3-certbot-nginx
   sudo certbot --nginx -d namamu.duckdns.org
   ```
   Certbot otomatis edit config Nginx & set auto-renew.
4. Update `GOOGLE_REDIRECT_URI` & `FRONTEND_URL` di `.env` ke `https://namamu.duckdns.org`, tambahkan URI itu juga di Google Cloud Console, lalu `php artisan config:clear` dan restart PHP-FPM.
