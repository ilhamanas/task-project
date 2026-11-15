# Dokumentasi Aplikasi Task Project

Dokumentasi teknis aplikasi. Perbarui setiap kali perilaku atau struktur data berubah.

Status dokumen: Fase 1, 2, dan 3 selesai (Laravel + Livewire + SortableJS, mencakup auth, RBAC per project, kanban, backlog, riwayat, komentar, lampiran, dan laporan).

## 1. Gambaran Umum

Aplikasi web untuk memonitor project berjalan, daftar fitur per project (kanban 4 status), dan backlog. Multi-user dengan akses dibatasi per project.

## 2. Peran Pengguna

| Peran | Hak |
|---|---|
| Admin | Mengelola user, semua project, dan keanggotaan project |
| Project Manager / PIC | Membuat project, mengatur fitur, memantau progress pada project yang diikutinya |
| Anggota tim | Melihat dan memperbarui status fitur pada project yang diikutinya |

## 3. Model Data

### `users`
| Kolom | Keterangan |
|---|---|
| id | |
| nama | |
| email | unik |
| password | di-hash |
| role | `admin`, `pm`, `member` |
| is_active | status aktif user (`true`/`false`) |
| avatar_path | path foto profil user di storage lokal privat (opsional) |

### `projects`
| Kolom | Keterangan |
|---|---|
| id | |
| nama | |
| deskripsi | |
| status | `planning`, `berjalan`, `selesai`, `ditunda` |
| pic_id | user penanggung jawab |
| tgl_mulai | |
| tgl_target | deadline |

### `project_members`
| Kolom | Keterangan |
|---|---|
| project_id | |
| user_id | |

Menentukan siapa yang boleh mengakses sebuah project.

### `features`
| Kolom | Keterangan |
|---|---|
| id | |
| project_id | |
| nama | |
| deskripsi | |
| status | `belum_mulai`, `dikerjakan`, `selesai`, `gagal` (default `belum_mulai`) |
| prioritas | `rendah`, `sedang`, `tinggi` |
| pic_id | |
| tgl_target | |
| tgl_mulai | terisi saat pindah ke `dikerjakan` |
| tgl_selesai | terisi saat pindah ke `selesai` |
| catatan_gagal | wajib saat status `gagal` |
| urutan | urutan dalam satu kolom kanban |

### `feature_logs`
| Kolom | Keterangan |
|---|---|
| id | |
| feature_id | |
| status_lama | `null` saat fitur baru dibuat |
| status_baru | |
| user_id | yang mengubah |
| created_at | |

### `feature_comments`
| Kolom | Keterangan |
|---|---|
| id | |
| feature_id | |
| user_id | penulis komentar |
| isi | isi komentar |
| created_at / updated_at | |

### `feature_attachments`
| Kolom | Keterangan |
|---|---|
| id | |
| feature_id | |
| user_id | pengunggah |
| nama_file | nama file asli |
| path | lokasi di storage lokal privat (`storage/app/feature-attachments`) |
| mime_type | |
| ukuran | ukuran dalam byte |
| created_at / updated_at | |

## 4. Aturan Bisnis

### Status fitur
- Fitur baru selalu berstatus `belum_mulai`.
- Pindah status lewat drag and drop di kanban atau lewat form.
- Pindah ke `gagal` wajib mengisi `catatan_gagal`.
- Setiap perubahan status dicatat di `feature_logs`.
- Tabel aturan perpindahan lengkap ada di `doc/workflow.md`.

### Progress project
```
progress (%) = jumlah fitur Selesai ÷ total fitur × 100
```
- Fitur Gagal tetap dihitung di total, tidak dihitung sebagai selesai.
- Jika project belum punya fitur, progress = 0%.
- Kartu angka dan progress bar diperbarui langsung setelah status berubah.

### Akses project
- User non-admin hanya melihat project yang ada di `project_members`.
- Berlaku untuk dashboard, daftar project, detail project, dan akses lewat URL.
- Project yang tidak boleh diakses: ditolak di server (403), bukan hanya disembunyikan.

### Project terlambat
Project ditandai terlambat jika `tgl_target` sudah lewat dan statusnya belum `selesai`.

## 5. Halaman

| Halaman | Isi |
|---|---|
| Login | Autentikasi user |
| Dashboard | 4 kartu status project (Planning, Berjalan, Selesai, Ditunda), daftar project, dan progres fitur keseluruhan sesuai akses user |
| Daftar Project | Tabel atau kartu project, filter, pencarian, kolom status dan deadline |
| Detail Project | 4 kartu status + progress bar, kanban, tabel fitur, backlog, riwayat, detail/komentar/lampiran fitur (sesuai project yang dipilih) |
| Laporan | Rekap progres per project, daftar fitur gagal, filter periode/status, dan ekspor CSV |
| Manajemen User | Khusus admin |
| Pengaturan Akun | Pengelolaan foto profil dan ubah kata sandi user yang sedang login (`/account`) |

## 6. Detail Project

Susunan halaman:

1. **Ringkasan**: 4 kartu angka (Belum Mulai, Dikerjakan, Selesai, Gagal) dan progress bar.
2. **Kanban**: 4 kolom, kartu fitur bisa digeser antar kolom.
3. **Tabel fitur**: alternatif tampilan dengan filter status.
4. **Backlog**: fitur Belum Mulai berurut prioritas (`?tab=backlog`).
5. **Riwayat**: log perubahan status (`?tab=riwayat`).

## 7. Kebutuhan Non-Fungsional

- Kinerja: dashboard dan detail project termuat kurang dari 2 detik untuk hingga 100 project dan 1.000 fitur.
- Keamanan: password di-hash, proteksi CSRF, pemeriksaan hak akses di server.
- Kegunaan: responsif di desktop dan HP, antarmuka berbahasa Indonesia.
- Keandalan: backup database berkala.

## 8. Setup Lokal

- **Stack**: Laravel Framework 10.50.4, Livewire v4.4.8, PHP 8.1.10 (Laragon), MySQL 8.0.30 (Laragon).
- **Instalasi dependency**:
  ```bash
  composer install
  ```
- **Konfigurasi environment**:
  Salin `.env.example` ke `.env`, lalu sesuaikan koneksi database (`DB_DATABASE=monitoring_project`, `DB_USERNAME=root`, `DB_PASSWORD=`) dan jalankan `php artisan key:generate`.
- **Migrasi dan seeder**:
  ```bash
  php artisan migrate --seed
  ```
- **Akun admin awal**:
  - Email: `admin@taskproject.test`
  - Password: `password`
  - Role: `admin`

## 9. Riwayat Perubahan Dokumen

| Tanggal | Perubahan |
|---|---|
| 14 Nov 2026 | Dokumen dibuat dari PRD v1.0 |
| 15 Nov 2026 | Dokumentasi disesuaikan dengan implementasi Fase 1, 2, dan 3 |
| 15 Nov 2026 | Tambah kolom `users.avatar_path` dan halaman Pengaturan Akun (`/account`) |
