# AGENTS.md

Panduan kerja untuk agent (coding agent) yang mengerjakan **Aplikasi Task Project**.
Baca file ini di awal setiap sesi, lalu baca `PROGRESS.md` untuk tahu posisi pekerjaan terakhir.

## 1. Ringkasan Proyek

Aplikasi web untuk memonitor project yang sedang berjalan: daftar project, daftar fitur per project, dan backlog.

- Fitur di setiap project memakai papan **kanban 4 status**: Belum Mulai, Dikerjakan, Selesai, Gagal.
- Fitur baru selalu masuk ke **Belum Mulai**. Saat dikerjakan digeser ke **Dikerjakan**.
- Ringkasan project: 4 kartu angka (satu per status) dan progress bar. Progress = Selesai ÷ total fitur.
- Multi-user. Setiap project hanya bisa diakses oleh user yang ditugaskan. Admin melihat semua project.
- Notifikasi tidak diperlukan pada fase awal.

Sumber kebenaran kebutuhan produk: dokumen **PRD - Aplikasi Task Project**.

## 2. Aturan Kerja Agent

1. Mulai sesi dengan membaca `AGENTS.md`, `PROGRESS.md`, dan `doc/workflow.md`, `prd.md`.
2. Kerjakan satu task kecil dalam satu waktu. Selesaikan dan uji sebelum pindah ke task berikutnya.
3. Jangan menambah fitur di luar PRD tanpa persetujuan. Kalau ada ide baru, catat di bagian "Pertanyaan Terbuka" di `PROGRESS.md`.
4. Setelah selesai mengerjakan sesuatu, perbarui `PROGRESS.md` (centang task dan tambah baris di log).
5. Jika kebutuhan tidak jelas, tanyakan ke pemilik proyek. Jangan menebak aturan bisnis.
6. Pemeriksaan hak akses wajib di sisi server. Menyembunyikan menu di tampilan tidak cukup.
7. Perubahan struktur data hanya lewat migration. Jangan mengubah database langsung.
8. Jika perilaku aplikasi berubah, perbarui `doc/documentation.md` pada commit yang sama.

## 3. Skills

### 3.1 Skill Terpasang

| Skill | Kapan dipakai | Sumber |
|---|---|---|
| **`using-superpowers`** | **Selalu, di awal setiap sesi.** Meta-skill yang memaksa pemanggilan skill yang relevan sebelum bertindak. | `obra/superpowers` |
| **`ponytail`** | Setiap tugas menulis kode: implementasi, fix, refactor, pilih dependency. Menekan over-engineering — kode paling sedikit yang benar-benar selesai. | `DietrichGebert/ponytail` |
| **`ponytail-review`** | Saat me-review diff/PR — cari kode berlebih, abstraksi tak perlu, duplikasi. | `DietrichGebert/ponytail` |
| **`antislop`** | **Selalu aktif saat membangun/mengedit UI.** Filter inti anti desain generik. | `miqdadbadjuber/anti-slop` |
| **`antislop-ui`** | Membangun komponen/halaman: warna, layout, motion, performa. | idem |
| **`antislop-human`** | Aksesibilitas: kontras, keyboard, focus, state, tap target. Wajib untuk komponen interaktif. | idem |
| **`antislop-layoutmobile`** | Layout responsif: grid, overflow, tap target, reflow phone→desktop. | idem |
| **`antislop-copywriting`** | Menulis/menyunting teks UI: headline, CTA, error message, empty state, microcopy. | idem |
| **`antislop-code`** | Higiene komentar kode — hapus komentar generik, pertahankan yang bernilai. | idem |
| **`ui-ux-pro-max`** | Keputusan UX yang lebih besar: alur, hierarki, pola interaksi, review desain. | `nextlevelbuilder/ui-ux-pro-max-skill` |
| **`find-skills`** | Mencari skill baru saat kebutuhan belum tercakup skill yang ada. | `vercel-labs/skills` |

## 4. Stack

| Bagian | Pilihan |
|---|---|
| Backend | Laravel |
| Database | MySQL |
| Frontend | Blade dengan Livewire atau Vue (belum diputuskan) |
| Drag and drop kanban | SortableJS |
| Deployment | Mengikuti server yang sudah dipakai (Apache atau Docker) |

## 5. Konvensi

- Bahasa antarmuka: Indonesia. Nama tabel, kolom, dan kode: Indonesia untuk istilah domain (`belum_mulai`, `dikerjakan`), Inggris untuk istilah teknis.
- Status fitur disimpan sebagai string: `belum_mulai`, `dikerjakan`, `selesai`, `gagal`.
- Perubahan status fitur selalu dicatat di `feature_logs` (status lama, status baru, user, waktu).
- Pindah ke `gagal` wajib mengisi `catatan_gagal`.
- Commit kecil dan jelas, format: `tipe: ringkasan` (contoh: `feat: kanban 4 status`, `fix: progress tidak menghitung fitur gagal`).

## 6. Struktur File Dokumentasi

```
AGENTS.md              panduan agent (file ini)
PROGRESS.md            status dan checklist pekerjaan
doc/
  prd.md               kebutuhan produk (salinan dari PRD di Claude Docs)
  workflow.md          alur kerja pengembangan
  documentation.md     dokumentasi teknis aplikasi
```
