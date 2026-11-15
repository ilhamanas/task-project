# PRD - Aplikasi Task Project

Dokumen kebutuhan produk untuk aplikasi monitoring project, fitur, dan backlog.

| | |
|---|---|
| Versi | 1.0 (draft) |
| Tanggal | 14 November 2026 |
| Status | Draft untuk review |

> Sumber utama PRD ini adalah dokumen di Claude Docs. File ini salinan untuk folder proyek. Jika keduanya berbeda, dokumen di Claude Docs yang berlaku.

## 1. Latar Belakang

Project yang berjalan sering dipantau lewat chat, catatan terpisah, atau spreadsheet, sehingga sulit melihat fitur mana yang belum dimulai, sedang dikerjakan, selesai, atau gagal. Aplikasi ini menyediakan satu tempat untuk memonitor semua project berjalan beserta daftar fitur dan backlog-nya, dengan tampilan kanban yang sederhana.

## 2. Tujuan dan Metrik Keberhasilan

**Tujuan**

- Melihat kondisi seluruh project berjalan dalam satu dashboard.
- Mengelola fitur setiap project dengan alur status yang jelas (kanban).
- Menyimpan ide dan fitur yang belum dijadwalkan di backlog.

**Metrik keberhasilan**

- Status semua project dapat dilihat dalam satu halaman tanpa membuka file lain.
- Mengubah status fitur cukup dengan satu aksi (drag and drop atau satu klik).
- Progress project selalu sesuai dengan jumlah fitur berstatus Selesai.

## 3. Pengguna

| Peran | Kebutuhan utama |
|---|---|
| Admin | Mengelola user, project, dan seluruh data |
| Project Manager / PIC | Membuat project, mengatur fitur, memantau progress |
| Anggota tim | Melihat fitur yang ditugaskan dan memperbarui statusnya |

Aplikasi dipakai multi-user dengan login dan peran di atas. Setiap project hanya dapat diakses oleh user yang ditugaskan; admin dapat melihat semua project.

## 4. Ruang Lingkup

**Termasuk (MVP)**

- Manajemen project (tambah, ubah, hapus, status project).
- Manajemen fitur per project dengan papan kanban 4 status.
- Backlog fitur per project.
- Dashboard ringkasan semua project.
- Login dan hak akses per project (user hanya melihat project yang ditugaskan).

**Di luar MVP (tahap berikutnya)**

- Notifikasi (email atau Telegram), tidak diperlukan pada fase awal.
- Komentar dan lampiran pada fitur.
- Sprint dan laporan periodik.
- Integrasi dengan GitHub atau tools lain.

## 5. Alur Status Fitur (Kanban)

Setiap fitur memiliki tepat satu status dari empat status berikut:

| Status | Arti |
|---|---|
| Belum Mulai | Fitur baru ditambahkan dan belum dikerjakan. Ini status awal. |
| Dikerjakan | Fitur sedang dalam pengerjaan. |
| Selesai | Fitur sudah rampung. |
| Gagal | Fitur tidak berhasil atau dibatalkan, wajib disertai alasan. |

**Aturan alur**

1. Fitur yang baru ditambahkan otomatis masuk ke **Belum Mulai**.
2. Saat mulai dikerjakan, fitur digeser ke **Dikerjakan**. Tanggal mulai terisi otomatis.
3. Dari Dikerjakan, fitur digeser ke **Selesai** (tanggal selesai terisi otomatis) atau **Gagal** (alasan wajib diisi).
4. Fitur Gagal dapat dibuka kembali dengan dikembalikan ke Belum Mulai atau Dikerjakan.
5. Fitur Selesai dapat dikembalikan ke Dikerjakan bila ternyata masih perlu perbaikan.

## 6. Kebutuhan Fungsional

### 6.1 Dashboard

- **FR-01**: Menampilkan daftar semua project berjalan beserta progress dan status.
- **FR-02**: Menampilkan total project per status (Planning, Berjalan, Selesai, Ditunda) di dashboard.
- **FR-03**: Menandai project yang melewati tanggal target.

### 6.2 Manajemen Project

- **FR-04**: Membuat, mengubah, dan menghapus project (nama, deskripsi, PIC, tanggal mulai, tanggal target).
- **FR-05**: Status project: Planning, Berjalan, Selesai, Ditunda.
- **FR-06**: Daftar project dapat dicari dan difilter berdasarkan status.

### 6.3 Detail Project

- **FR-07**: Bagian ringkasan di bagian atas berisi 4 kartu angka (Belum Mulai, Dikerjakan, Selesai, Gagal) dan progress bar.
- **FR-08**: Progress dihitung otomatis: **Selesai ÷ total fitur**. Fitur Gagal tetap dihitung dalam total tetapi tidak dihitung sebagai selesai.
- **FR-09**: Tersedia dua tampilan fitur: papan kanban dan tabel daftar, dengan filter status.
- **FR-10**: Fitur baru yang ditambahkan selalu masuk ke kolom Belum Mulai.
- **FR-11**: Fitur dapat digeser antar kolom kanban (drag and drop) untuk mengubah status.
- **FR-12**: Kartu angka dan progress bar diperbarui langsung setelah status berubah.

### 6.4 Manajemen Fitur

- **FR-13**: Tambah, ubah, dan hapus fitur (nama, deskripsi, prioritas, PIC, tanggal target).
- **FR-14**: Prioritas: Rendah, Sedang, Tinggi.
- **FR-15**: Alasan wajib diisi saat fitur dipindah ke Gagal.
- **FR-16**: Urutan fitur dalam satu kolom dapat diatur manual.

### 6.5 Backlog

- **FR-17**: Backlog menampilkan fitur berstatus Belum Mulai, diurutkan berdasarkan prioritas.
- **FR-18**: Fitur dapat ditambahkan langsung dari halaman backlog.
- **FR-19**: Fitur dari backlog dapat dipindah ke Dikerjakan saat siap dikerjakan.

### 6.6 Riwayat Perubahan

- **FR-20**: Setiap perubahan status dicatat (fitur, status lama, status baru, siapa, kapan).

### 6.7 User dan Hak Akses

- **FR-21**: Login dengan email dan password.
- **FR-22**: Admin mengelola user (tambah, ubah, nonaktifkan).
- **FR-23**: Admin menentukan user yang menjadi anggota setiap project (tabel `project_members`).
- **FR-24**: User hanya melihat project tempat ia menjadi anggota, di dashboard, daftar project, dan detail project. Project lain tidak muncul.
- **FR-25**: Akses langsung ke project lewat URL oleh user yang bukan anggota ditolak. Pemeriksaan dilakukan di sisi server.
- **FR-26**: Anggota tim dapat menambah dan memperbarui fitur hanya pada project yang diikutinya. Admin dapat mengakses semua project.

## 7. Kebutuhan Non-Fungsional

- **Kinerja**: halaman dashboard dan detail project termuat kurang dari 2 detik untuk hingga 100 project dan 1.000 fitur.
- **Keamanan**: password di-hash, proteksi CSRF, dan pemeriksaan hak akses di sisi server.
- **Kegunaan**: responsif di desktop dan HP, antarmuka dalam bahasa Indonesia.
- **Keandalan**: backup database berkala.

## 8. Model Data

**users**: id, nama, email, password, role

**projects**: id, nama, deskripsi, status, pic_id, tgl_mulai, tgl_target

**project_members**: project_id, user_id

**features**: id, project_id, nama, deskripsi, status (`belum_mulai`, `dikerjakan`, `selesai`, `gagal`), prioritas, pic_id, tgl_target, tgl_mulai, tgl_selesai, catatan_gagal, urutan

**feature_logs**: id, feature_id, status_lama, status_baru, user_id, created_at

## 9. Halaman Aplikasi

| Halaman | Isi |
|---|---|
| Login | Autentikasi user |
| Dashboard | Ringkasan semua project |
| Daftar Project | Tabel atau kartu project, filter, pencarian, dengan kolom status project dan tanggal deadline |
| Detail Project | Ringkasan 4 kartu + progress, kanban, tabel fitur, backlog, riwayat (sesuai project yang dipilih) |
| Manajemen User | Khusus admin |

## 10. Usulan Teknologi

- Backend: Laravel dengan MySQL.
- Frontend: Blade dengan Livewire atau Vue, drag and drop kanban memakai SortableJS.
- Deployment: sesuai server yang sudah dipakai (Apache atau Docker).

## 11. Rencana Rilis

| Tahap | Isi |
|---|---|
| Fase 1 | Login, hak akses per project, project, fitur, kanban 4 status, ringkasan progress |
| Fase 2 | Backlog, riwayat perubahan |
| Fase 3 | Notifikasi, komentar, lampiran, laporan |

## 12. Pertanyaan Terbuka

1. Apakah fitur Gagal perlu dikecualikan dari total saat menghitung progress? Usulan: tetap dihitung di total, tidak dihitung selesai (sesuai FR-08).
2. Apakah perlu pengelompokan fitur ke dalam sprint atau fase?

## 13. Keputusan

- Aplikasi multi-user, dengan akses terbatas per project (FR-21 sampai FR-26).
- Notifikasi tidak diperlukan pada fase awal.
