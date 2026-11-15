# PROGRESS.md

Status pekerjaan **Aplikasi Task Project**. Perbarui file ini setiap selesai mengerjakan task.

Terakhir diperbarui: 15 November 2026

## Status Saat Ini

- Fase aktif: **Fase 1, 2, dan 3 selesai**
- Tahap: seluruh fitur di checklist (`Fase 1`, `Fase 2`, `Fase 3`) selesai diimplementasikan dan diuji (`20 passed`).

## Fase 1: Inti Aplikasi

### Setup
- [x] Inisialisasi proyek Laravel dan koneksi MySQL
- [x] Layout dasar dan navigasi
- [x] Setup seeder (admin awal)

### Database
- [x] Migration `users` (nama, email, password, role)
- [x] Migration `projects`
- [x] Migration `project_members`
- [x] Migration `features` (status, prioritas, pic, tanggal, catatan_gagal, urutan)
- [x] Migration `feature_logs`

### Login dan Hak Akses
- [x] Login dengan email dan password
- [x] Role: admin, project manager, anggota tim
- [x] Manajemen user (khusus admin)
- [x] Admin menentukan anggota setiap project
- [x] Filter project berdasarkan keanggotaan (dashboard, daftar, detail)
- [x] Akses URL project oleh non-anggota ditolak di sisi server

### Project
- [x] CRUD project (nama, deskripsi, PIC, tanggal mulai, tanggal target)
- [x] Status project: Planning, Berjalan, Selesai, Ditunda
- [x] Daftar project dengan filter dan pencarian, kolom status dan deadline
- [x] Dashboard ringkasan semua project

### Fitur dan Kanban
- [x] CRUD fitur (nama, deskripsi, prioritas, PIC, tanggal target)
- [x] Fitur baru otomatis masuk Belum Mulai
- [x] Kanban 4 kolom: Belum Mulai, Dikerjakan, Selesai, Gagal
- [x] Drag and drop mengubah status
- [x] Tanggal mulai dan selesai terisi otomatis saat status berubah
- [x] Alasan wajib saat pindah ke Gagal
- [x] 4 kartu angka dan progress bar di detail project (Selesai ÷ total fitur)
- [x] Tampilan tabel daftar fitur dengan filter status

## Fase 2: Backlog dan Riwayat

- [x] Halaman backlog (fitur Belum Mulai, urut prioritas)
- [x] Tambah fitur langsung dari backlog
- [x] Pindah fitur dari backlog ke Dikerjakan
- [x] Riwayat perubahan status (`feature_logs`) tampil di detail project

## Fase 3: Pengembangan Lanjutan

- [x] Komentar pada fitur
- [x] Lampiran pada fitur
- [x] Laporan

Catatan: notifikasi (email atau Telegram) tidak diperlukan pada fase awal.

## Keputusan

| Tanggal | Keputusan |
|---|---|
| 14 Nov 2026 | Aplikasi multi-user, akses dibatasi per project |
| 14 Nov 2026 | Notifikasi tidak diperlukan pada fase awal |
| 14 Nov 2026 | Fitur baru masuk Belum Mulai, digeser ke Dikerjakan saat dikerjakan |
| 14 Nov 2026 | Progress = Selesai ÷ total fitur |
| 15 Nov 2026 | Frontend memakai Blade + Livewire |
| 15 Nov 2026 | Referensi desain dashboard memakai Dasher UI (https://preview.themewagon.com/dasher-ui/) |
| 15 Nov 2026 | 4 kartu ringkasan di Dashboard menampilkan status project (Planning, Berjalan, Selesai, Ditunda) |

## Pertanyaan Terbuka

1. Apakah fitur Gagal dikecualikan dari total saat menghitung progress? Usulan: tetap dihitung di total, tidak dihitung selesai.
2. Apakah perlu pengelompokan fitur ke dalam sprint atau fase?
3. Backlog masuk MVP (bagian Ruang Lingkup PRD) atau Fase 2 (Rencana Rilis PRD)? Dua bagian PRD itu belum sama.

## Log Pekerjaan

| Tanggal | Pekerjaan | Oleh |
|---|---|---|
| 14 Nov 2026 | PRD v1.0 dibuat dan dirapikan (akses per project masuk Fase 1) | Claude |
| 14 Nov 2026 | Dibuat `AGENTS.md`, `PROGRESS.md`, `doc/workflow.md`, `doc/documentation.md` | Claude |
| 15 Nov 2026 | Inisialisasi Laravel 10.50.4 + Livewire 4.4.8 dan koneksi MySQL `monitoring_project` | Agent |
| 15 Nov 2026 | Migration tabel `users`, `projects`, `project_members`, `features`, `feature_logs` | Agent |
| 15 Nov 2026 | Setup seeder akun admin awal (`admin@taskproject.test`) | Agent |
| 15 Nov 2026 | Layout dasar dan navigasi Blade + Livewire bergaya Dasher UI | Agent |
| 15 Nov 2026 | Autentikasi login/logout dengan email, password, dan cek `is_active` | Agent |
| 15 Nov 2026 | Role (`admin`, `pm`, `member`), middleware `role`, dan Manajemen User (khusus admin) | Agent |
| 15 Nov 2026 | Model `Project`, `Feature`, `ProjectPolicy`, CRUD Project, daftar & filter project, penugasan `project_members`, dan proteksi URL `403` | Agent |
| 15 Nov 2026 | Dashboard ringkasan semua project & total fitur per status bergaya Dasher UI | Agent |
| 15 Nov 2026 | Model `FeatureLog`, logika transisi status fitur, 4 kartu + progress bar, CRUD fitur, dan tabel daftar fitur di Detail Project | Agent |
| 15 Nov 2026 | Papan Kanban 4 kolom + SortableJS drag and drop, pengurutan manual kolom, dan toggle tampilan Kanban/Tabel | Agent |
| 15 Nov 2026 | Fase 2: Tab Backlog (urut prioritas, tambah fitur, mulai kerjakan) dan Riwayat `feature_logs` di Detail Project | Agent |
| 15 Nov 2026 | Fase 3: Migration & model `feature_comments` serta modal Detail & Komentar Fitur | Agent |
| 15 Nov 2026 | Fase 3: Migration & model `feature_attachments`, upload/hapus di detail fitur, dan unduhan terproteksi | Agent |
| 15 Nov 2026 | Fase 3: Halaman Laporan project & fitur (filter keanggotaan, rentang tanggal, ekspor CSV) | Agent |
| 15 Nov 2026 | Ubah 4 kartu utama Dashboard menjadi rekap status project (Planning, Berjalan, Selesai, Ditunda) | Agent |
| 15 Nov 2026 | Tambah halaman custom error 404 (ilustrasi Dasher UI) dan 403 berbahasa Indonesia | Agent |
| 15 Nov 2026 | Pasang logo Rinjan di halaman login dan icon brand (`icon.png`) sebagai favicon di semua halaman & sidebar | Agent |
| 15 Nov 2026 | Tambah menu & halaman Pengaturan Akun (`/account`) untuk update foto profil (`avatar_path`) dan ubah kata sandi | Agent |
