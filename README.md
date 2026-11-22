# Fourji Graduation — Client Photo Proofing System

A luxury, minimal photo proofing web application crafted for **Fourji Graduation**. Photographers upload high-resolution graduation photos to Google Drive, synchronize the metadata, and share a private link with the client. Clients browse a fast, elegant gallery, select their favorite photos up to their package quota, add retouching notes, and submit. The photographer can then copy the selected filenames in one click to filter directly in Adobe Lightroom.

---

## ⚡ Key Architectural Features

- **Google Drive API v3 (Direct Signed Edge CDN & Local Cache Proxy):** Images stream directly from Google's high-speed signed CDN (`thumbnailLink` with `=s600` and `=s1600`) with an automatic fallback proxy (`/photos/{id}/stream`) with local thumbnail caching.
- **Multi-Format & RAW Support:** Full support for standard images (`.jpg`, `.png`, `.webp`, `.heic`) as well as Camera RAW & Adobe DNG (`.dng`, `.cr2`, `.cr3`, `.arw`, `.nef`, `.raf`).
- **Dual Google Auth Modes:** Connect directly with photographer's personal Google Drive account via 1-click OAuth 2.0 or via Service Account (`service-account.json`).
- **Service Layer Isolation:** `app/Services/GoogleDriveService.php` handles automatic pagination (`nextPageToken`) for 500+ photos per folder and idempotent upserts that preserve existing client selections and retouching notes.
- **Strict Quota & Concurrency Protection:** Selection toggling runs inside database transactions with row-level locks (`lockForUpdate`), preventing race conditions and quota overflows.
- **PIN Protection:** Optional PIN access via `VerifyGalleryPin` middleware.
- **1-Click Adobe Lightroom Export:** Generates formatted filename queries (`JER00004.DNG, JER00012.JPG`) and filename-only queries (`JER00004, JER00012`) ready to paste into Lightroom's Library Filter in under 3 seconds.
- **Minimalist Luxury Editorial UI:** Monochromatic palette (Pure White `#FFFFFF`, Obsidian Black `#09090b`), GLightbox modal preview, Alpine.js live counter, mobile floating dock, and CSS watermark overlay.

---

## 🚀 Getting Started with Laragon

### 1. Database Setup
1. Start MySQL in Laragon.
2. Create a database named `fourji_proofing` (e.g. via HeidiSQL or phpMyAdmin).
3. Run migrations and database seeder:
   ```bash
   php artisan migrate --seed
   ```

### 2. Default Admin Credentials
- **URL:** [http://fourjigraduation.test/login](http://fourjigraduation.test/login) or `http://localhost:8000/login`
- **Email:** `admin@fourji.id`
- **Password:** `password`

### 3. Google Drive Service Account Key
1. In Google Cloud Console, enable **Google Drive API** and create a **Service Account**.
2. Generate a **JSON key** and save it to:
   ```text
   storage/app/google/service-account.json
   ```
3. Share your photoshoot Google Drive folder with the Service Account email as **Viewer**.
4. Set link sharing to **"Anyone with the link can view"** so the CDN thumbnail endpoints (`lh3.googleusercontent.com`) can render previews without browser session restrictions.

---

## 🛠 Tech Stack

- **Backend:** Laravel 13 / PHP 8.3
- **Authentication:** Laravel Breeze
- **Database:** MySQL
- **External Integration:** Google Drive API v3 (`google/apiclient`)
- **Frontend:** Laravel Blade, Tailwind CSS, Alpine.js, GLightbox
