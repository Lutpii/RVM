# Deploy RVM ke Raspberry Pi 4 (mesin kiosk mandiri)

Panduan ini mengasumsikan Raspberry Pi 4 kamu **sudah ada OS-nya dan bisa diakses lewat SSH**. Semua perintah di bawah dijalankan **di Pi** (lewat SSH), bukan di laptop.

Ditulis untuk konfigurasi yang sudah diverifikasi: user `adi`, home `/home/adi/RVM`, Raspberry Pi OS berbasis **Debian 13 "trixie"** (PHP default 8.4), kamera `ov5647` terdeteksi lewat `rpicam-hello`. Kalau environment kamu beda, ganti `adi` / `/home/adi/RVM` / versi PHP di seluruh contoh. File-file config siap pakai ada di folder [`deploy/`](../deploy/) di root repo ini.

---

## 0. Prasyarat ✅ (sudah diverifikasi)

- `uname -m` → `aarch64` ✅ (64-bit, wajib untuk PyTorch/ultralytics)
- `rpicam-hello --list-cameras` → kamera `ov5647` terdeteksi ✅
- `VERSION_CODENAME=trixie` (Debian 13) — PHP default **8.4**, dipakai di seluruh panduan ini.

Panduan ini juga mengasumsikan Pi jalan versi **Desktop** (bukan Lite) — dibutuhkan untuk mode kiosk (Chromium fullscreen) di langkah 8. Cek dengan `echo $XDG_SESSION_TYPE` (harus `x11` atau `wayland`, bukan kosong).

---

## 1. Install paket sistem

```bash
sudo apt update && sudo apt full-upgrade -y

sudo apt install -y \
  nginx mariadb-server \
  php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-gd \
  composer \
  python3-venv python3-pip python3-picamera2 \
  git

# Node.js 18+ (apt bawaan biasanya versi lama, pakai NodeSource)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

> **`pigpio` tidak ada di repo apt** di Debian/Raspberry Pi OS versi ini (project-nya sudah tidak dimaintain upstream) — install manual dari source (`git clone https://github.com/joan2937/pigpio`, `make`, `sudo make install`) kalau belum punya. Lalu daftarkan sebagai systemd service supaya jalan permanen & auto-start tiap boot:
> ```bash
> sudo pkill pigpiod   # matikan instance manual yang mungkin lagi jalan
> sudo cp ~/RVM/deploy/pigpiod.service /etc/systemd/system/pigpiod.service
> sudo systemctl daemon-reload
> sudo systemctl enable --now pigpiod
> systemctl status pigpiod   # pastikan "active (running)"
> ```

> **Chromium untuk mode kiosk** — nama paketnya beda-beda antar versi OS (`chromium` atau `chromium-browser`). Coba dulu:
> ```bash
> sudo apt install -y chromium
> which chromium || which chromium-browser
> ```
> Kalau `chromium` tidak ada, pakai `chromium-browser`. Catat mana yang berhasil — dipakai lagi di langkah 8.

Kalau ada nama paket `php8.4-*` yang gagal ("Unable to locate package"), jalankan `apt-cache search php-fpm` dan kirim hasilnya ke saya — berarti versi PHP di repo kamu beda dari dugaan, saya sesuaikan lagi.

---

## 2. Permission GPIO & kamera

```bash
sudo usermod -aG gpio,video adi
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

`database/rvm_db.sql` adalah export bersih (data user sudah dummy, token API dikosongkan) dari database dev — sudah termasuk skema terbaru (kolom `kiosk_token`, `ai_confidence`) **dan** tabel `migrations` itu sendiri, jadi `php artisan migrate` nanti tidak akan bentrok "table already exists" kalau dijalankan lagi.

**Login demo setelah import**: semua akun pakai password `password` — admin di `admin@rvm.com`, user biasa di `user2@example.com` sampai `user14@example.com`.

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

sudo chown -R adi:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

> Kalau `composer install` komplain soal versi PHP (platform requirement) — PHP 8.4 lebih baru dari yang biasa dites Laravel 10 — kirim error-nya ke saya, kemungkinan cuma perlu update 1-2 dependency lewat `composer update` yang aman, bukan masalah besar.

---

## 5. AI service (kamera + YOLO + servo)

`ultralytics`/`torch` itu paket besar (ratusan MB–GB) dan gampang bikin kartu SD kehabisan ruang — apalagi kalau `pip install torch` biasa ikut menarik paket CUDA NVIDIA yang sama sekali tidak berguna di Pi. Kalau kamu **sudah punya venv lain yang berhasil** (mis. dari eksperimen `test_yolo.py` sebelumnya) dengan `torch`/`ultralytics`/`picamera2`/`opencv` terpasang, **pakai venv itu langsung** — jangan buat venv baru di sini, cukup arahkan `rvm-ai.service` ke situ (lihat `ExecStart` di [`deploy/rvm-ai.service`](../deploy/rvm-ai.service), sesuaikan path-nya kalau beda).

Cek dulu venv lama itu punya `flask` + `flask-cors` asli (bukan cuma `types-Flask-Cors`):
```bash
/path/ke/venv-lama/bin/python3 -c "import flask, flask_cors; print('OK')"
```
Kalau error, install ke venv itu langsung: `/path/ke/venv-lama/bin/pip install flask flask-cors`.

**Kalau belum ada venv sama sekali**, baru buat baru — pakai CPU-only torch dari awal supaya tidak kena masalah CUDA/space:
```bash
cd ~/RVM/BackEnd/ai_service
python3 -m venv venv --system-site-packages
source venv/bin/activate
pip install torch --index-url https://download.pytorch.org/whl/cpu
pip install -r requirements.txt
pip install gpiozero
deactivate
```

Taruh file model di `~/RVM/BackEnd/ai_service/model/best_exp6.pt` (model yang benar-benar dipakai, 3 kelas) — `app.py` mencari `best_exp6.pt` lebih dulu daripada `best.pt`, baik di root project maupun di `BackEnd/ai_service/model/`. Lihat urutan lengkapnya di `app.py` (`_MODEL_CANDIDATES`) atau README bagian [AI Service](../README.md#-ai-service--yolov8-model).

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

Generate sertifikat self-signed (untuk kiosk LAN internal — lihat catatan upgrade ke cert asli di langkah 10):
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

---

## 8. Mode kiosk (fullscreen di layar Pi)

```bash
mkdir -p ~/.config/autostart
cp ~/RVM/deploy/rvm-kiosk-autostart.desktop ~/.config/autostart/
```
Ganti `RVM-001` di file itu dengan `machine_code` mesin fisik ini kalau berbeda. Kalau di Step 1 ternyata binary-nya `chromium` bukan `chromium-browser`, edit juga baris `Exec=` di file itu.

Matikan screen blanking supaya layar kiosk tidak tidur. Raspberry Pi OS sejak Bookworm/trixie pakai **Wayland** (bukan X11/LXDE lagi), jadi `xset` tidak berlaku — pakai `raspi-config`:
```bash
sudo raspi-config
```
Pilih **Display Options** → **Screen Blanking** → **No** → **Finish**.

---

## 9. Reboot & verifikasi end-to-end

```bash
sudo reboot
```

Setelah nyala lagi:
```bash
systemctl status nginx mariadb php8.4-fpm pigpiod rvm-ai
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
