# ♻️ Reverse Vending Machine (RVM) - Full Stack Web System

A complete smart recycling management system built with **Laravel 10 + Vue.js 3 SPA** and **Python Flask YOLOv8n AI** classification.

---

## 📁 Project Structure

```
vending/
├── FrontEnd/          ← Vue.js 3 SPA (Vite)
│   ├── src/
│   │   ├── views/     ← All pages (Landing, Login, Register, Dashboard, Session, Summary, Admin)
│   │   ├── store/     ← Pinia state (auth, rvm)
│   │   ├── router/    ← Vue Router SPA navigation
│   │   ├── services/  ← Axios API client
│   │   └── locales/   ← English + Bahasa Indonesia
│   └── package.json
│
├── BackEnd/           ← Laravel 10 REST API
│   ├── app/
│   │   ├── Http/Controllers/   ← Auth, Session, Transaction, QR, Admin, User, Machine
│   │   ├── Models/             ← Eloquent models
│   │   └── Services/           ← FonnteService (WhatsApp), AiService (YOLOv8)
│   ├── routes/api.php
│   ├── ai_service/    ← Python Flask + YOLOv8n
│   │   ├── app.py
│   │   └── requirements.txt
│   └── .env.example
│
└── database/
    └── rvm_db.sql     ← MySQL schema + seed data
```

---

## ⚡ Quick Setup (Laragon)

### Step 1: Database Setup
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Create new database: `rvm_db`
3. Import: `database/rvm_db.sql`

### Step 2: Laravel Backend Setup
```bash
# Place the vending/ folder inside: C:\laragon\www\vending\

cd C:\laragon\www\vending\BackEnd

# Install PHP dependencies
composer install

# Copy env file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations (optional - if not using SQL file)
php artisan migrate --seed
```

Edit `.env` with your credentials:
```env
DB_DATABASE=rvm_db
DB_USERNAME=root
DB_PASSWORD=

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost/vending/BackEnd/public/auth/google/callback

FONNTE_API_TOKEN=your_fonnte_token
FONNTE_SENDER=your_whatsapp_number
```

### Step 3: Python AI Service Setup
```bash
cd BackEnd/ai_service

# Install Python dependencies
pip install -r requirements.txt

# Place your trained YOLOv8 model:
# BackEnd/ai_service/model/rvm_model.pt

# Start AI service
python app.py
# Runs on http://localhost:5000
```

> **Note:** The AI service runs in **MOCK mode** if no model file is found.  
> Place your `.pt` model at: `BackEnd/ai_service/model/rvm_model.pt`

### Step 4: Frontend Setup
```bash
cd FrontEnd

# Install Node dependencies
npm install

# Start development server
npm run dev
# Opens at http://localhost:5173

# OR build for production (copies to Laravel public folder)
npm run build
```

### Step 5: Access the System
| URL | Description |
|-----|-------------|
| `http://localhost:5173` | Vue.js dev server (development) |
| `http://localhost/vending/BackEnd/public` | Laravel API (Laragon) |
| `http://localhost/phpmyadmin` | phpMyAdmin database |
| `http://localhost:5000/health` | AI service health check |

---

## 🔑 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@rvm.com` | `password` |
| User | `emma@example.com` | `password` |

---

## 🔒 Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create a new project → Enable **Google+ API**
3. Create **OAuth 2.0 Client ID**
4. Add redirect URI: `http://localhost/vending/BackEnd/public/api/auth/google/callback`
5. Copy `Client ID` and `Client Secret` to `.env`

---

## 📱 WhatsApp OTP (Fonnte)

1. Register at [fonnte.com](https://fonnte.com)
2. Connect your WhatsApp number
3. Copy API token to `.env` → `FONNTE_API_TOKEN`
4. Set your number → `FONNTE_SENDER`

---

## 🤖 YOLOv8n Model Integration

The AI service expects a YOLOv8n model trained on **4 classes**:
| Class ID | Material |
|----------|----------|
| 0 | aluminum |
| 1 | plastic |
| 2 | glass |
| 3 | paper |

**To use your trained model:**
```bash
# Copy your model
cp your_model.pt BackEnd/ai_service/model/rvm_model.pt

# Restart AI service
python BackEnd/ai_service/app.py
```

**Test the AI service:**
```bash
curl -X POST http://localhost:5000/classify \
  -H "X-API-Key: rvm_ai_secret_key_2024" \
  -F "image=@test_image.jpg"
```

---

## 🌐 API Endpoints Reference

### Auth
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register with email/phone |
| POST | `/api/auth/login` | Login with email/password |
| POST | `/api/auth/send-otp` | Send WhatsApp OTP |
| POST | `/api/auth/verify-otp` | Verify WhatsApp OTP |
| GET  | `/api/auth/google/redirect` | Google OAuth URL |
| GET  | `/api/auth/me` | Get current user |
| POST | `/api/auth/logout` | Logout |

### RVM Flow
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET  | `/api/qr/generate/{machineCode}` | Generate QR for machine |
| POST | `/api/qr/scan` | User scans QR |
| POST | `/api/sessions/start` | Start recycling session |
| POST | `/api/sessions/{code}/end` | End session |
| GET  | `/api/sessions/{code}/summary` | Get session summary |
| POST | `/api/transactions/check-bin` | Step 1: Check bin |
| POST | `/api/transactions/classify` | Step 6: AI classify |
| POST | `/api/transactions/weigh` | Step 7: Weigh item |
| POST | `/api/transactions/complete` | Step 8: Complete |
| POST | `/api/transactions/reject` | Step 8b: Reject item |

### Points Calculation
```
100g  = base unit
Points per 100g:
  Aluminum = 10 pts
  Plastic  = 8 pts
  Glass    = 6 pts
  Paper    = 5 pts

Invalid item = -10 pts deducted
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Vue.js 3 + Vite + Vue Router + Pinia |
| Styling | Pure CSS with CSS Variables (Dark/Light) |
| Backend | Laravel 10 (PHP 8.1) |
| Database | MySQL 8.0 via phpMyAdmin |
| Auth | Laravel Sanctum + Google OAuth (Socialite) |
| WhatsApp | Fonnte API |
| AI | Python 3.10 + Flask + YOLOv8n (Ultralytics) |
| Server | Laragon (Apache) |

---

## 🌍 Language Support
- **English** (default)
- **Bahasa Indonesia**

Toggle with the `EN / ID` button on any page.

---

## 🎨 Features
- ✅ Dark / Light theme toggle
- ✅ Bilingual (EN / ID)
- ✅ Google OAuth login
- ✅ Email + password auth
- ✅ WhatsApp OTP verification (Fonnte)
- ✅ QR code session linking
- ✅ Full RVM recycling flow (14 steps)
- ✅ AI material classification (YOLOv8n)
- ✅ Points system (earned / deducted)
- ✅ Bin level monitoring
- ✅ RVM locator with Google Maps
- ✅ Session summary
- ✅ Admin panel (users, machines, sessions, stats)

---

*Built for Laragon + phpMyAdmin environment*
