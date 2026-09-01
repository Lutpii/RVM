# ♻️ Reverse Vending Machine (RVM) — Full Stack Web System

A smart recycling management system: **Laravel 10 REST API** + **Vue.js 3 SPA** (Vite) + a **Python Flask / YOLOv8** microservice for AI material classification.

---

## 📁 Project Structure

```
RVM/
├── FrontEnd/                 ← Vue.js 3 SPA (Vite)
│   ├── src/
│   │   ├── views/             ← Pages (Landing, Login, Register, Dashboard, Kiosk, Scan, Session, Summary, Admin, Settings)
│   │   ├── store/              ← Pinia state (auth, rvm)
│   │   ├── router/              ← Vue Router (hash mode)
│   │   ├── services/            ← Axios API client
│   │   └── locales/              ← English + Bahasa Indonesia
│   └── package.json
│
├── BackEnd/                  ← Laravel 10 REST API
│   ├── app/
│   │   ├── Http/Controllers/  ← Auth, Session, Transaction, QR, Machine, User, Admin
│   │   ├── Models/              ← Eloquent models
│   │   └── Services/             ← FonnteService (WhatsApp OTP), AiService (YOLOv8 client)
│   ├── routes/api.php
│   ├── ai_service/            ← Python Flask + YOLOv8 microservice
│   │   ├── app.py
│   │   ├── model/               ← working copies of trained weights (see AI Service below)
│   │   └── requirements.txt
│   └── .env.example
│
├── database/
│   └── rvm_db.sql             ← MySQL schema + demo seed data (see caveat below)
│
├── deploy/                   ← Nginx / systemd / kiosk-autostart configs for the Raspberry Pi target
├── docs/                      ← DEPLOY_RASPBERRY_PI.md and other reference docs
├── backup/                    ← archived trained model weights (best.pt, best_exp6.pt)
└── materials/                 ← course report/presentation material (not required to run the app)
```

> `materials/` (`generate_ppt.py`, `presentation*.html`) holds supporting report/presentation material for the course project — not required to run the web app itself. Trained model weights (`best.pt`, `best_exp6.pt`) are archived in `backup/`; the AI service actually loads its working copy from `BackEnd/ai_service/model/`, see [AI Service](#-ai-service--yolov8-model) below.

---

## ✅ Prerequisites

| Tool | Version | Notes |
|------|---------|-------|
| PHP | 8.1+ | with `mysqli`/`pdo_mysql` extension |
| Composer | 2.x | PHP dependency manager |
| Node.js | 18+ | includes npm |
| Python | 3.10+ | for the AI classification service |
| MySQL | 8.0 | via Laragon, XAMPP, or a native install |

---

## ⚡ Setup

You need **3 things running at once** during development: the Laravel API, the Python AI service, and the Vue dev server.

### Step 1 — Clone & database

```bash
git clone https://github.com/Lutpii/RVM.git
cd RVM
```

Create an empty database (e.g. via phpMyAdmin or the MySQL CLI):

```sql
CREATE DATABASE rvm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Populate it — pick one:**

- **Option A — fresh schema (always matches the current code, no demo data)**
  ```bash
  cd BackEnd
  php artisan migrate
  ```
  You'll then need to register a user through the app and manually promote it to admin (see [Default Login Credentials](#-default-login-credentials) below).

- **Option B — import the demo dataset (fastest, comes with ready-made accounts + sample machines)**
  Import `database/rvm_db.sql` (phpMyAdmin → *Import*, or `mysql -u root rvm_db < database/rvm_db.sql`).
  This snapshot predates two small later migrations, so run these two statements once afterwards to bring it fully up to date:
  ```sql
  ALTER TABLE qr_sessions  ADD COLUMN kiosk_token   VARCHAR(64)   NULL UNIQUE AFTER qr_token;
  ALTER TABLE transactions ADD COLUMN ai_confidence DECIMAL(5,4) NULL        AFTER ai_detected_type;
  ```
  (Without this patch, the QR/kiosk scan flow and stored AI confidence values won't work correctly.)

### Step 2 — Laravel backend

```bash
cd BackEnd
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and fill in at least:

| Variable | Description |
|----------|-------------|
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Your MySQL credentials (`DB_DATABASE=rvm_db` by default) |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` | Only needed for "Sign in with Google" — see [Google OAuth Setup](#-google-oauth-setup) |
| `FONNTE_API_TOKEN` / `FONNTE_SENDER` | Only needed for WhatsApp OTP — see [WhatsApp OTP](#-whatsapp-otp-fonnte) |
| `AI_SERVICE_URL` / `AI_SERVICE_KEY` | Where the Flask AI service runs — defaults are fine for local dev |
| `FRONTEND_URL` | Where the Vue dev server runs (`https://localhost:5173` by default) — used to build the Google OAuth redirect and the QR-code deep link, **not** for CORS (CORS is wide open in `config/cors.php`) |

Then start the API:

```bash
php artisan serve
# API available at http://localhost:8000
```

> `FrontEnd/vite.config.js` proxies `/api` to `http://127.0.0.1:8000`, so `php artisan serve` is what the frontend dev server expects out of the box. If you'd rather serve the backend through Apache/Laragon's virtual host, update that proxy target in `vite.config.js` to match.

### Step 3 — Python AI service

```bash
cd BackEnd/ai_service
pip install -r requirements.txt
python app.py
# Runs on http://localhost:5000
```

See [AI Service / YOLOv8 model](#-ai-service--yolov8-model) below — without a trained model file, this runs in **mock mode** (random classifications), which is fine for testing the rest of the flow.

### Step 4 — Vue frontend

```bash
cd FrontEnd
npm install
npm run dev
# Opens at https://localhost:5173
```

The dev server uses a self-signed HTTPS certificate (via `@vitejs/plugin-basic-ssl`) — your browser will show a "not secure" / certificate warning the first time. Click **Advanced → Proceed** to continue; this is expected for local development.

For a production build: `npm run build` outputs static files to `FrontEnd/dist/` (it does **not** automatically copy into Laravel's `public/` folder — deploy/serve that folder however fits your hosting setup, or point `VITE_API_URL` at your backend's absolute URL if frontend and backend are hosted separately).

### Step 5 — Access the system

| URL | Description |
|-----|-------------|
| `https://localhost:5173` | Vue SPA (development) |
| `http://localhost:8000` | Laravel API (`php artisan serve`) |
| `http://localhost:8000/api/...` | REST API base path |
| `http://localhost:5000/health` | AI service health check |

---

## 🔑 Default Login Credentials

Only available if you imported `database/rvm_db.sql` ([Option B](#step-1--clone--database)):

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@rvm.com` | `password` |
| User | `emma@example.com` | `password` |

If you migrated from scratch ([Option A](#step-1--clone--database)) instead, register a new account from the app's Register page, then promote it to admin manually:

```sql
UPDATE users SET role = 'admin', is_verified = 1 WHERE email = 'you@example.com';
```

---

## 🔒 Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create a project → enable the Google Identity/OAuth API
3. Create an **OAuth 2.0 Client ID** (Web application)
4. Add an authorized redirect URI matching `GOOGLE_REDIRECT_URI` in your `.env` (e.g. `http://127.0.0.1:8000/auth/google/callback`)
5. Copy the **Client ID** and **Client Secret** into `.env`

---

## 📱 WhatsApp OTP (Fonnte)

1. Register at [fonnte.com](https://fonnte.com)
2. Connect your WhatsApp number to a Fonnte device
3. Copy the API token into `.env` → `FONNTE_API_TOKEN`
4. Set your sending number → `FONNTE_SENDER`

---

## 🤖 AI Service / YOLOv8 model

`BackEnd/ai_service/app.py` looks for a trained model in this order, using the first one that exists:

1. `<repo root>/best_exp6.pt`
2. `BackEnd/ai_service/model/best_exp6.pt` ← **the model actually in use** (3 classes: `aluminium can`, `glass bottle`, `plastic bottle`)
3. `<repo root>/best.pt`
4. `BackEnd/ai_service/model/best.pt` (older 4-class fallback: `aluminum`, `plastic`, `glass`, `paper`)

This repo keeps its trained weights out of the repo root — `best.pt` and `best_exp6.pt` live in `backup/`, with a working copy of `best_exp6.pt` placed at `BackEnd/ai_service/model/best_exp6.pt` so candidate #2 above resolves. `*.pt` files are excluded from git (large binaries), so **a fresh clone from GitHub has no model file** — the AI service will log a warning and automatically run in **mock mode**, returning a random material + confidence for every classification request. This lets you exercise the entire recycling flow end-to-end without a real model.

Class names come from the model itself when a model is loaded; `['aluminum', 'plastic', 'glass', 'paper']` is only used as a fallback label list in mock mode.

**To use a real trained model**, place your weights at any of the paths above (matching filename matters — `best_exp6.pt` takes priority over `best.pt`) and restart `python app.py`.

**Test the AI service directly:**
```bash
curl -X POST http://localhost:5000/classify \
  -H "X-API-Key: rvm_ai_secret_key_2024" \
  -F "image=@test_image.jpg"
```

---

## 🌐 API Endpoints Reference

### Auth (public)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register with email and/or phone |
| POST | `/api/auth/login` | Login with email/phone + password |
| POST | `/api/auth/send-otp` | Send WhatsApp OTP |
| POST | `/api/auth/verify-otp` | Verify WhatsApp OTP |
| POST | `/api/auth/refresh` | Refresh session |
| GET  | `/api/auth/google/redirect` | Get Google OAuth redirect URL |

### QR / Machines (public)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/qr/generate/{machineCode}` | Generate a session QR code for a machine (kiosk display) |
| GET | `/api/qr/status/{token}` | Poll whether a QR token has been scanned |
| GET | `/api/machines` | List machines |
| GET | `/api/machines/{id}` | Machine detail |

### Authenticated (Sanctum token, and/or kiosk token via `X-Kiosk-Token` header)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/logout` | Logout |
| GET  | `/api/auth/me` | Current user |
| POST | `/api/qr/scan` | User scans a machine's QR code |
| GET/PUT | `/api/user/profile` | Get/update profile |
| GET | `/api/user/points-history` | Points history |
| GET | `/api/user/sessions` | Past recycling sessions |
| POST | `/api/sessions/start` | Start a recycling session |
| GET | `/api/sessions/{sessionCode}` | Session detail |
| POST | `/api/sessions/{sessionCode}/end` | End a session |
| GET | `/api/sessions/{sessionCode}/summary` | Session summary |
| POST | `/api/transactions/check-bin` | Step: check bin capacity for chosen material |
| POST | `/api/transactions/open-lid` | Step: open lid |
| POST | `/api/transactions/insert-item` | Step: item inserted |
| POST | `/api/transactions/process-conveyor` | Step: run conveyor |
| POST | `/api/transactions/capture-image` | Step: capture item image |
| POST | `/api/transactions/classify` | Step: AI classification |
| POST | `/api/transactions/weigh` | Step: weigh item |
| POST | `/api/transactions/complete` | Step: complete + award points |
| POST | `/api/transactions/reject` | Step: reject invalid item (deducts points) |

### Admin only (`admin` middleware)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/dashboard` | Dashboard stats |
| GET/PUT/DELETE | `/api/admin/users`, `/api/admin/users/{id}` | Manage users |
| GET/POST/PUT/DELETE | `/api/admin/machines`, `/api/admin/machines/{id}` | Manage machines |
| PUT | `/api/admin/machines/{id}/bin-levels` | Update bin fill levels |
| GET | `/api/admin/sessions` | All sessions |
| GET | `/api/admin/transactions` | All transactions |
| GET | `/api/admin/stats` | Aggregate stats |
| GET | `/api/admin/logs` | Admin action log |
| GET/PUT | `/api/admin/reward-config` | View/update per-material point rates |
| POST | `/api/admin/reset-bin-alerts` | Reset bins that are ≥90% full |
| GET | `/api/admin/export-csv` | Export data as CSV |
| GET | `/api/admin/chart-data` | Chart/analytics data |

---

## 💰 Points System

Defined in `TransactionController::calcPoints()` — every valid recycled item (any material) earns a random amount between `POINTS_MIN` and `POINTS_MAX`, currently **15–20 points** (conversion baseline: 100 points = RM 1).

- Rejected / invalid items deduct **10 points**.
- Admins can view/adjust suggested reward rates per material from the Admin panel (`/api/admin/reward-config`), stored in `BackEnd/storage/app/reward_config.json`.

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Vue.js 3 + Vite + Vue Router (hash mode) + Pinia + vue-i18n |
| Charts | Chart.js / vue-chartjs |
| Backend | Laravel 10 (PHP 8.1) |
| Database | MySQL 8.0 |
| Auth | Laravel Sanctum + Google OAuth (Socialite) |
| WhatsApp | Fonnte API |
| AI | Python 3.10+ + Flask + YOLOv8 (Ultralytics) |

---

## 🌍 Language Support

English (default) and Bahasa Indonesia — toggle with the `EN / ID` button on any page.

---

## 🎨 Features

- Dark / light theme toggle
- Bilingual (EN / ID)
- Google OAuth + email/password auth
- WhatsApp OTP verification (Fonnte)
- QR code session linking (kiosk flow)
- Full RVM recycling flow (bin check → lid → insert → conveyor → capture → classify → weigh → complete/reject)
- AI material classification (YOLOv8, with mock-mode fallback)
- Points system (earned / deducted) with admin-configurable rates
- Bin level monitoring + reset alerts
- RVM locator with machine coordinates
- Session summary
- Admin panel (users, machines, sessions, transactions, stats, CSV export)

---

## 🔌 Hardware Integration

`BackEnd/ai_service/app.py` can drive real hardware on a Raspberry Pi — a Picamera2 camera (`POST /capture`) and two sorting servos (`POST /sort`), ported from the standalone test rig in `BackEnd/ai_service/test_yolo.py`. It falls back to software-only mode automatically when those libraries aren't available (e.g. on a dev laptop), so the rest of the app is unaffected either way.

For a full production deployment to a Raspberry Pi 4 (Nginx, MariaDB, systemd services, kiosk-mode Chromium), see **[docs/DEPLOY_RASPBERRY_PI.md](docs/DEPLOY_RASPBERRY_PI.md)**.

---

## 🩹 Troubleshooting

| Symptom | Likely cause / fix |
|---------|---------------------|
| Browser blocks `https://localhost:5173` with a certificate warning | Expected — Vite's dev HTTPS cert is self-signed. Click through the "Advanced" warning once. |
| Frontend can't reach the API (`/api/...` 404 or network error) | Make sure `php artisan serve` is running on port 8000 — the Vite proxy is hardcoded to `127.0.0.1:8000` in `FrontEnd/vite.config.js`. |
| AI classification always returns random results | No model file found — this is **mock mode**, not a bug. Add a `.pt` file (see [AI Service](#-ai-service--yolov8-model)). |
| QR scan / kiosk login doesn't work after importing `rvm_db.sql` | You likely skipped the two `ALTER TABLE` statements in [Step 1, Option B](#step-1--clone--database) — the dump predates the `kiosk_token` column. |
| `php artisan migrate` fails with "table already exists" | You mixed the two database options — either import the SQL dump **or** run migrations from an empty database, not both. |
| Google login redirects to the wrong place | Check `FRONTEND_URL` and `GOOGLE_REDIRECT_URI` in `.env` match what you registered in Google Cloud Console. |

---

*Course project — Laravel + Vue.js + YOLOv8.*
