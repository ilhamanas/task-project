# Workflow

Alur kerja pengembangan **Aplikasi Task Project**, untuk developer maupun agent.

## 1. Alur Kerja Harian

1. Baca `AGENTS.md` dan `PROGRESS.md`. Cari task berikutnya yang belum dicentang di fase aktif.
2. Pastikan kebutuhan task jelas. Kalau belum, tanyakan sebelum menulis kode.
3. Buat branch: `feat/<nama-singkat>`, `fix/<nama-singkat>`, atau `docs/<nama-singkat>`.
4. Kerjakan task kecil, tulis kode seminimal mungkin yang benar-benar menyelesaikan task.
5. Uji secara manual dan, bila ada, jalankan test otomatis.
6. Perbarui dokumentasi jika perilaku berubah (`doc/documentation.md`).
7. Centang task di `PROGRESS.md` dan tambah satu baris di Log Pekerjaan.
8. Commit, lalu review diff sebelum digabung.

## 2. Definisi Selesai

Sebuah task dianggap selesai jika:

- [ ] Perilaku sesuai PRD.
- [ ] Hak akses diperiksa di sisi server (bila menyentuh data project).
- [ ] Tampilan berfungsi di desktop dan HP.
- [ ] Tidak ada error di log.
- [ ] `PROGRESS.md` dan `doc/documentation.md` diperbarui.

## 3. Alur Status Fitur (Kanban)

Aturan yang harus dipatuhi kode aplikasi:

| Dari | Ke | Aturan |
|---|---|---|
| (baru) | Belum Mulai | Otomatis saat fitur dibuat |
| Belum Mulai | Dikerjakan | `tgl_mulai` terisi otomatis |
| Dikerjakan | Selesai | `tgl_selesai` terisi otomatis |
| Dikerjakan | Gagal | `catatan_gagal` wajib diisi |
| Gagal | Belum Mulai / Dikerjakan | Dibuka kembali, `catatan_gagal` tetap tersimpan sebagai riwayat |
| Selesai | Dikerjakan | Untuk perbaikan, `tgl_selesai` dikosongkan |

Setiap perpindahan dicatat di `feature_logs`.

## 4. Alur Hak Akses

1. Admin membuat user dan menetapkan role.
2. Admin atau project manager menambahkan user ke project (`project_members`).
3. User hanya melihat project tempat ia menjadi anggota. Project lain tidak muncul di daftar maupun dashboard.
4. Akses langsung lewat URL oleh non-anggota ditolak di server.
5. Admin dapat mengakses semua project.

## 5. Konvensi Commit

Format: `tipe: ringkasan singkat`

| Tipe | Dipakai untuk |
|---|---|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `refactor` | Perubahan kode tanpa mengubah perilaku |
| `docs` | Perubahan dokumentasi |
| `chore` | Setup, dependency, konfigurasi |

## 6. Review

- Review diff sebelum digabung: cari kode berlebih, abstraksi yang tidak perlu, dan duplikasi.
- Periksa bahwa setiap query data project memfilter berdasarkan keanggotaan user.
- Periksa teks antarmuka: singkat, jelas, bahasa Indonesia.

## 7. Menangani Perubahan Kebutuhan

1. Catat perubahan di bagian Pertanyaan Terbuka atau Keputusan di `PROGRESS.md`.
2. Perbarui PRD setelah keputusan dibuat.
3. Sesuaikan checklist fase yang terdampak.
