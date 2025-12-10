# 🚀 Panduan Deploy ke Railway

## Langkah 1: Upload ke GitHub

```bash
cd c:\xampp\htdocs\Kontrakan
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/USERNAME/kontrakan.git
git push -u origin main
```

## Langkah 2: Buat Project di Railway

1. Buka [railway.app](https://railway.app)
2. Login dengan **GitHub**
3. Klik **"New Project"**
4. Pilih **"Deploy from GitHub repo"**
5. Pilih repo `kontrakan`
6. Railway akan auto-detect PHP dan mulai deploy

## Langkah 3: Tambah MySQL Database

1. Di project Railway, klik **"+ New"**
2. Pilih **"Database"** → **MySQL**
3. Railway akan auto-create database dan set environment variables
4. Variables ini otomatis tersedia: `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD`

## Langkah 4: Import Schema

1. Di Railway, klik MySQL service
2. Tab **"Data"** atau **"Query"**
3. Copy-paste isi `database/schema.sql` (hapus baris CREATE DATABASE dan USE)
4. Jalankan query

Atau connect via CLI:
```bash
railway connect mysql
# Lalu paste SQL
```

## Langkah 5: Generate Domain

1. Klik PHP service
2. Tab **"Settings"** → **"Networking"**
3. Klik **"Generate Domain"**
4. Dapat URL seperti `kontrakan-production.up.railway.app`

## Langkah 6: Test

1. Buka URL Railway
2. Login: `admin` / `kontrakan123`
3. Test semua fitur!

---

## 🔄 Auto-Deploy

Setiap kali push ke GitHub main branch, Railway otomatis deploy ulang!

```bash
git add .
git commit -m "Update fitur"
git push
# Railway auto-deploy dalam ~1-2 menit
```

---

## ⚠️ Penting

- Ganti password default sebelum pakai production
- Railway punya $5 free trial credit
- Setelah trial habis, perlu upgrade ke Hobby plan ($5/bulan)

## 🔧 Troubleshooting

| Error | Solusi |
|-------|--------|
| Build failed | Cek logs di Railway |
| Database error | Pastikan MySQL service sudah running |
| 404 | Pastikan struktur folder benar |
