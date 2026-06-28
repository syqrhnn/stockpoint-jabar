# Product Requirements Document (PRD)
## StockPoint Jabar — Sistem Informasi Manajemen Persediaan & Reorder Point Multi-Gudang

| Field | Detail |
|---|---|
| Versi Dokumen | 1.0 |
| Tanggal | 28 Juni 2026 |
| Disusun Oleh | Ahmad Syauqi Raihan |
| Referensi | Project Charter v1.1, SRS v1.3, SCRUM Worksheet Sprint 1 |
| Status | Ready for Development |

---

## 1. Ringkasan Produk

StockPoint Jabar adalah sistem informasi manajemen persediaan berbasis web untuk PT Suplai Nusantara Regional. Sistem ini menggantikan pencatatan stok manual (spreadsheet terpisah per gudang) dengan satu platform terpusat yang dapat diakses oleh seluruh pemangku kepentingan di tiga gudang operasional: Bandung, Bekasi, dan Bogor.

**Dua kapabilitas inti:**
1. **Visibilitas stok real-time** — satu dashboard untuk memantau kondisi persediaan dari semua gudang sekaligus.
2. **Reorder Point otomatis** — sistem menghitung kapan harus pesan ulang ke supplier berdasarkan formula `ROP = (ADU × Lead Time) + Safety Stock`.

**Target keberhasilan utama:**
- Waktu Manajer Operasional mendapat data stok berkurang ≥ 50%
- Kejadian stockout berkurang ≥ 30% dalam 3 bulan pertama
- ≥ 80% pengguna dapat mengoperasikan sistem tanpa pelatihan khusus

---

## 2. Konteks & Masalah

### 2.1 Kondisi Saat Ini (As-Is)
- Setiap gudang mencatat stok di spreadsheet masing-masing — tidak ada sinkronisasi antar gudang
- Manajer Operasional harus menghubungi kepala gudang satu per satu via telepon/WA untuk mendapat data stok terkini (butuh waktu berjam-jam, data sering sudah basi)
- Keputusan reorder sepenuhnya berbasis intuisi staf — tidak ada metode terukur
- Akibatnya: stockout dan overstock bergantian terjadi

### 2.2 Solusi (To-Be)
- Satu platform web terpusat — semua gudang input ke sistem yang sama
- Dashboard real-time yang menampilkan ringkasan stok dari 3 gudang dalam hitungan detik
- ROP dihitung otomatis oleh sistem; notifikasi muncul otomatis saat stok kritis

### 2.3 Batasan Keras (Out of Scope)
- Tidak ada versi mobile (desktop-only)
- Tidak ada integrasi ERP, sistem akuntansi, atau API eksternal
- Tidak ada prediksi AI/ML
- Tidak ada IoT / sensor gudang
- Tidak ada modul keuangan (faktur, invoice, pembayaran)
- Notifikasi hanya in-app (tidak ada email/SMS/WhatsApp)
- Cakupan hanya 3 gudang yang sudah ditetapkan

---

## 3. Pengguna & Hak Akses

### 3.1 Daftar Role

| Role | Persona | Frekuensi | Hak Akses Utama |
|---|---|---|---|
| **Admin Gudang** | Koordinator sistem / IT | Sesuai kebutuhan | Full access semua modul, semua gudang |
| **Kepala Gudang** | Penanggung jawab gudang | Setiap hari kerja | Semua fitur operasional di gudang sendiri; konfigurasi ROP |
| **Staf Gudang** | Operator harian | Beberapa kali/hari | Input transaksi stok & lihat riwayat di gudang sendiri |
| **Manajer Operasional** | Level manajemen | Setiap hari kerja | Read-only semua gudang; export laporan |

### 3.2 Matriks Hak Akses

| Modul / Aksi | Admin | Kepala Gudang | Staf Gudang | Manajer Ops |
|---|:---:|:---:|:---:|:---:|
| Manajemen Pengguna (CRUD) | ✓ | — | — | — |
| Manajemen Data Master (CRUD) | ✓ | — | — | — |
| Input Stok Masuk | ✓ | ✓ | ✓ | — |
| Input Stok Keluar | ✓ | ✓ | ✓ | — |
| Stock Adjustment | ✓ | ✓ (gudang sendiri) | — | — |
| Konfigurasi Parameter ROP | ✓ | ✓ (gudang sendiri) | — | — |
| Dashboard — semua gudang | ✓ | — | — | ✓ |
| Dashboard — gudang sendiri | ✓ | ✓ | ✓ | — |
| Export Laporan — semua gudang | ✓ | — | — | ✓ |
| Export Laporan — gudang sendiri | ✓ | ✓ | — | — |
| Lihat Notifikasi | ✓ | ✓ | ✓ | ✓ |

---

## 4. Arsitektur Sistem

### 4.1 Tech Stack

| Layer | Teknologi |
|---|---|
| Frontend | HTML, CSS, JavaScript, Bootstrap |
| Backend | PHP Laravel |
| Database | MySQL 8.x |
| Web Server | Nginx atau Apache |
| Server | VPS / Cloud Server — Linux Ubuntu LTS |
| Autentikasi | Session-based (Laravel Session Middleware) |
| API | RESTful API |
| Version Control | Git + GitHub |
| CI/CD | Pipeline otomatis (build + deploy) |
| Browser | Chrome, Firefox, Edge (versi terbaru) |

### 4.2 Arsitektur Deployment

```
Browser Pengguna (Bandung / Bekasi / Bogor)
        │
        │ HTTPS / TLS 1.2–1.3
        ▼
Web Server (Nginx / Apache)
        │
        │ FastCGI / PHP-FPM
        ▼
App Server — PHP Laravel
        │
        │ Eloquent ORM (loopback)
        ▼
Database Server — MySQL (stockpoint_jabar)
```

### 4.3 Skema Database (9 Tabel Utama)

| Tabel | Deskripsi |
|---|---|
| `users` | Akun pengguna; kolom: id, nama, email, password_hash, role, gudang_id, is_active |
| `gudang` | Data 3 gudang; kolom: id, nama, lokasi, kapasitas |
| `barang` | Data produk; kolom: id, nama, kategori, satuan |
| `supplier` | Data pemasok; kolom: id, nama, kontak, lead_time_default |
| `stok` | Saldo stok terkini per barang per gudang; kolom: id, barang_id, gudang_id, saldo, status |
| `transaksi_stok` | Riwayat semua transaksi; kolom: id, barang_id, gudang_id, jenis (masuk/keluar/adjustment), jumlah, tanggal, supplier_id, user_id |
| `rop_parameter` | Konfigurasi ROP per barang per gudang; kolom: id, barang_id, gudang_id, adu, lead_time, safety_stock, rop, updated_by |
| `notifikasi` | Notifikasi in-app stok kritis; kolom: id, barang_id, gudang_id, pesan, status (belum_dibaca/sudah_dibaca) |
| `log_audit` | Catatan permanen aksi sensitif; kolom: id, transaksi_stok_id, user_id, aksi, alasan, timestamp |

---

## 5. Fitur & Requirements

### 5.1 Modul 1 — Autentikasi & Manajemen Pengguna

**Fitur yang diperlukan:**

| ID | Fitur | Prioritas | Deskripsi |
|---|---|---|---|
| FR-001 | Buat Akun | High | Admin membuat akun baru (nama, email, password, role, gudang) |
| FR-002 | Ubah Akun | High | Admin mengubah data akun (nama, email, role, gudang) |
| FR-003 | Nonaktifkan Akun | High | Admin menonaktifkan akun; session aktif pengguna diakhiri otomatis |
| FR-004 | Login | High | Semua pengguna login via email + password; diarahkan ke dashboard sesuai role |
| FR-005 | Logout | High | Session dihancurkan; pengguna kembali ke halaman login |
| FR-006 | Menu Berbasis Role | High | Sidebar navigasi berbeda untuk tiap role; dibuat dinamis dari session |

**Business Rules:**
- Password disimpan sebagai bcrypt hash (cost factor ≥ 10), tidak pernah plaintext
- Akun dengan `is_active = 0` ditolak saat login meskipun kredensial benar
- Error login: pesan generik tanpa mengindikasikan field mana yang salah
- RBAC ditegakkan di server (bukan hanya UI) — akses langsung ke URL role lain → HTTP 403

---

### 5.2 Modul 2 — Manajemen Data Master

**Fitur yang diperlukan:**

| ID | Fitur | Prioritas | Deskripsi |
|---|---|---|---|
| FR-007 | Tambah Barang | High | Admin menambah barang baru (nama, kategori, satuan) |
| FR-008 | Ubah Barang | High | Admin mengubah data barang |
| FR-009 | Hapus Barang | Medium | Admin menghapus barang — **hanya jika belum ada riwayat transaksi** |
| FR-010 | Kelola Data Gudang | High | Admin menambah/ubah gudang (nama, lokasi, kapasitas) |
| FR-011 | Kelola Data Supplier | High | Admin menambah/ubah supplier (nama, kontak, lead time default) |
| FR-012 | Cari & Filter Data Master | Medium | Semua pengguna dapat mencari data dengan pencarian real-time |

**Business Rules:**
- Barang yang sudah memiliki riwayat transaksi tidak dapat dihapus — sistem menolak dengan pesan spesifik
- Pencarian real-time tanpa full page reload

---

### 5.3 Modul 3 — Pencatatan Stok

**Fitur yang diperlukan:**

| ID | Fitur | Prioritas | Deskripsi |
|---|---|---|---|
| FR-013 | Catat Stok Masuk | High | Input penerimaan barang dari supplier (barang, jumlah, tanggal, supplier) |
| FR-014 | Catat Stok Keluar | High | Input pengeluaran barang dengan validasi ketersediaan stok |
| FR-015 | Update Saldo Otomatis | High | Sistem memperbarui saldo stok otomatis setiap transaksi |
| FR-016 | Hitung Ulang ADU & ROP | High | Setiap transaksi keluar memicu recalculation ADU → ROP → status stok |
| FR-017 | Stock Adjustment | High | Koreksi saldo stok oleh Kepala/Admin — wajib isi alasan — tercatat di log_audit |
| FR-018 | Riwayat Transaksi | Medium | Filter riwayat per gudang, barang, jenis transaksi, tanggal |

**Business Rules:**
- Transaksi keluar yang melebihi saldo ditolak sistem ("stok tidak mencukupi")
- Tanggal transaksi tidak boleh di masa mendatang
- Satu transaksi = satu barang di satu gudang
- Semua transaksi bersifat permanen (tidak bisa dihapus); koreksi via Stock Adjustment
- Stock Adjustment: alasan wajib diisi; entri di log_audit immutable

---

### 5.4 Modul 4 — Perhitungan Reorder Point (ROP Engine)

**Fitur yang diperlukan:**

| ID | Fitur | Prioritas | Deskripsi |
|---|---|---|---|
| FR-019 | Konfigurasi Parameter ROP | High | Kepala/Admin input Lead Time (hari) dan Safety Stock (unit) per barang per gudang |
| FR-020 | Hitung ROP Otomatis | High | Sistem menghitung `ROP = (ADU × Lead Time) + Safety Stock` |
| FR-021 | Evaluasi Status Stok | High | Sistem menentukan status Aman/Menipis/Kritis secara otomatis |
| FR-022 | Visualisasi Status ROP | Medium | Halaman menampilkan perbandingan stok aktual vs nilai ROP |

**Formula & Business Rules:**

```
ADU (Average Daily Usage) = Total stok keluar 30 hari terakhir ÷ 30
  → Dihitung per barang per gudang
  → Jika tidak ada transaksi keluar 30 hari: ADU = 0

ROP = (ADU × Lead Time) + Safety Stock
  → Lead Time: bilangan bulat positif > 0 (satuan: hari)
  → Safety Stock: bilangan bulat ≥ 0 (satuan: unit)

Status Stok:
  → AMAN    : stok > ROP + (20% × ROP)
  → MENIPIS : ROP < stok ≤ ROP + (20% × ROP)
  → KRITIS  : stok ≤ ROP

Jika parameter ROP belum dikonfigurasi → status: "Belum Dikonfigurasi"
```

**Pemicu Recalculation:**
- Setiap transaksi stok keluar baru
- Setiap perubahan parameter Lead Time atau Safety Stock

---

### 5.5 Modul 5 — Notifikasi

**Fitur yang diperlukan:**

| ID | Fitur | Prioritas | Deskripsi |
|---|---|---|---|
| FR-023 | Buat Notifikasi Otomatis | High | Sistem membuat notifikasi saat status stok berubah menjadi Kritis |
| FR-024 | Lihat Notifikasi (Bell Icon) | High | Dropdown notifikasi dengan badge jumlah yang belum dibaca |
| FR-025 | Tandai Sudah Dibaca | Medium | Pengguna menandai notifikasi; badge berkurang |
| FR-026 | Riwayat Notifikasi | Medium | Halaman daftar semua notifikasi historis |

**Business Rules:**
- Notifikasi dibuat **hanya** saat status berubah menjadi Kritis (bukan Menipis → Aman, dll.)
- Notifikasi juga dibuat saat opening balance langsung masuk kondisi Kritis (jika ROP sudah dikonfigurasi)
- Notifikasi tidak dapat dihapus — hanya bisa ditandai sudah dibaca
- Badge di header menampilkan jumlah notifikasi belum dibaca; hilang jika semua sudah dibaca

---

### 5.6 Modul 6 — Dashboard Monitoring

**Fitur yang diperlukan:**

| ID | Fitur | Prioritas | Deskripsi |
|---|---|---|---|
| FR-027 | Dashboard Semua Gudang | High | Admin & Manajer Ops: lihat ringkasan persediaan 3 gudang sekaligus |
| FR-028 | Dashboard Gudang Sendiri | High | Kepala Gudang: hanya lihat data gudangnya sendiri |
| FR-029 | Filter Dashboard | Medium | Filter per gudang, barang, atau status stok |
| FR-030 | Detail Stok dari Dashboard | Medium | Klik barang → lihat detail stok & riwayat transaksi |

**Konten Dashboard (Minimum):**
- Ringkasan stok per gudang (total item, item Kritis, item Menipis)
- Daftar barang prioritas (status Kritis dan Menipis di bagian atas)
- Grafik pergerakan stok (tren)
- Status ROP setiap barang
- Notifikasi terbaru

---

### 5.7 Modul 7 — Laporan & Export

**Fitur yang diperlukan:**

| ID | Fitur | Prioritas | Deskripsi |
|---|---|---|---|
| FR-031 | Generate Laporan Stok Harian | Medium | Laporan saldo stok terkini per gudang dengan filter |
| FR-032 | Generate Laporan Riwayat Transaksi | Medium | Laporan semua transaksi dalam periode tertentu |
| FR-033 | Generate Laporan Status ROP | Medium | Laporan status ROP seluruh barang per gudang |
| FR-034 | Export ke PDF | Low | Unduh laporan dalam format PDF |
| FR-035 | Export ke Excel | Low | Unduh laporan dalam format .xlsx |

**Filter yang tersedia:** periode waktu, gudang, barang, jenis transaksi, status stok

---

## 6. Non-Functional Requirements

| Kategori | Requirement |
|---|---|
| **Performance** | Response time maksimal 3 detik untuk setiap request utama, termasuk dashboard multi-gudang |
| **Availability** | Sistem tersedia minimal 95% selama jam operasional (Senin–Sabtu 07.00–17.00 WIB) |
| **Security** | HTTPS (TLS 1.2/1.3); bcrypt password; RBAC middleware; parameterized query (anti SQL injection); session-based auth |
| **Usability** | ≥ 80% pengguna operasional dapat menyelesaikan tugas utama tanpa bantuan tambahan |
| **Reliability** | Tidak ada crash pada concurrent users dari 3 gudang; tidak ada bug Critical/High saat Go-Live |
| **Scalability** | Arsitektur memungkinkan penambahan gudang baru tanpa rebuild dari awal |
| **Compatibility** | Berjalan di Chrome, Firefox, Edge versi terbaru (desktop only) |
| **Backup** | Backup database otomatis minimal 1x/hari |
| **Audit** | Log audit immutable untuk semua aksi sensitif (Stock Adjustment, ubah pengguna, ubah parameter ROP) |

---

## 7. Product Backlog (Terurut Prioritas)

| ID | Epic | User Story | Prioritas | Estimasi |
|---|---|---|---|---|
| PB01 | Auth & Akses | Login dengan email/password + tampilan menu sesuai role | High | 1 hari |
| PB02 | Auth & Akses | Logout aman (session berakhir) | High | 0,5 hari |
| PB03 | Data Master | Admin kelola data Barang, Gudang, Supplier (CRUD + validasi) | High | 3 hari |
| PB04 | Manajemen Pengguna | Admin kelola akun pengguna (buat, ubah, nonaktifkan) | High | 2 hari |
| PB05 | Pencatatan Stok | Staf catat stok masuk dari supplier → saldo update otomatis | High | 2 hari |
| PB06 | Pencatatan Stok | Staf catat stok keluar dengan validasi ketersediaan | High | 2 hari |
| PB07 | Pencatatan Stok | Kepala/Admin koreksi saldo stok + audit log | High | 2 hari |
| PB08 | Monitoring Dashboard | Dashboard multi-gudang (Admin & Manajer Ops) + filter | High | 4 hari |
| PB09 | ROP Engine | Kepala/Admin konfigurasi Lead Time & Safety Stock per barang per gudang | Medium | 2 hari |
| PB10 | ROP Engine | Sistem hitung ADU otomatis + recalculate ROP & status setiap transaksi keluar | Medium | 3 hari |
| PB11 | Notifikasi | Notifikasi otomatis saat stok Kritis + bell icon + tandai dibaca | Medium | 3 hari |
| PB12 | Riwayat | Filter riwayat transaksi (gudang, barang, jenis, tanggal) | Medium | 2 hari |
| PB13 | Laporan & Export | Generate laporan + export PDF & Excel | Low | 3 hari |
| PB14 | Data Master | Input opening balance (saldo awal per barang per gudang saat Go-Live) | High | 1 hari |

**Total estimasi pengembangan:** ~31 hari kerja

---

## 8. Sprint Plan

### Sprint 1 (25 Mei – 7 Juni 2026) — Foundation
**Goal:** Sistem sudah bisa diakses dengan login berbasis role; Admin dapat mengelola seluruh data referensi.

**Items:** PB01, PB02, PB03
- Autentikasi (login, logout, session, RBAC middleware)
- CRUD Barang, Gudang, Supplier

**Status Sprint 1 (hasil simulasi):**
- PB01 ✅ Done — Login + sidebar dinamis per 4 role + URL protection
- PB02 ✅ Done — Logout, session bersih
- PB03 ⚠️ Partial — CRUD Barang & Gudang selesai; integrasi UI Supplier ke API belum tuntas

**Carry-over ke Sprint 2:** Penyelesaian UI Supplier (PB03)

### Sprint 2 — Operasional Stok
**Goal:** Transaksi stok masuk/keluar berjalan; Admin dapat mengelola akun pengguna.

**Items:** Sisa PB03 (Supplier UI), PB04, PB05, PB06

### Sprint 3 — ROP & Monitoring
**Goal:** Sistem menghitung ROP otomatis; dashboard monitoring real-time tersedia.

**Items:** PB07, PB08, PB09, PB10

### Sprint 4 — Notifikasi, Laporan & Go-Live Prep
**Goal:** Notifikasi stok kritis aktif; laporan bisa diekspor; data awal diinput.

**Items:** PB11, PB12, PB13, PB14

---

## 9. Definition of Done

Sebuah fitur dinyatakan **Done** apabila memenuhi **semua** kriteria berikut:

1. ✅ Dikembangkan sesuai spesifikasi FR dan SRS
2. ✅ Terintegrasi antara frontend dan backend (data dapat disimpan, diproses, ditampilkan dengan benar)
3. ✅ Lolos seluruh skenario pengujian QA tanpa bug severity Critical atau High
4. ✅ Tampilan sesuai wireframe yang disetujui
5. ✅ Berjalan di Chrome, Firefox, Edge versi terbaru tanpa masalah kompatibilitas
6. ✅ Dokumentasi penggunaan fitur tersedia dan dapat dipahami pengguna tanpa penjelasan tambahan

---

## 10. UAT Scenarios (Ringkasan)

| Kode | Skenario | Hasil yang Diharapkan |
|---|---|---|
| UAT-001 | Admin buat akun Staf Gudang baru | Akun tersimpan; pengguna baru bisa login |
| UAT-002 | Admin nonaktifkan akun aktif | Status nonaktif; pengguna tidak bisa login |
| UAT-003 | Login dengan akun nonaktif | Ditolak; pesan error tampil |
| UAT-004 | Admin tambah barang baru | Tersimpan dan muncul di semua modul |
| UAT-005 | Admin hapus barang bertransaksi | Ditolak; pesan error spesifik tampil |
| UAT-006 | Staf catat stok masuk valid | Transaksi tersimpan; saldo bertambah |
| UAT-007 | Staf catat stok keluar melebihi saldo | Ditolak; "stok tidak mencukupi" |
| UAT-008 | Stok keluar menyebabkan saldo ≤ ROP | Transaksi tersimpan; status → Kritis; notifikasi dibuat |
| UAT-009 | Stock Adjustment tanpa alasan | Ditolak; field alasan wajib diisi |
| UAT-010 | Stock Adjustment dengan alasan valid | Koreksi tersimpan; saldo diperbarui; log_audit tercatat |
| UAT-011 | Kepala konfigurasi Lead Time & Safety Stock | ROP dihitung; status stok dievaluasi ulang |
| UAT-012 | Buka ikon lonceng setelah ada stok Kritis | Dropdown notifikasi muncul; badge tampil |
| UAT-013 | Tandai notifikasi sudah dibaca | Status berubah; badge berkurang |
| UAT-014 | Manajer Ops buka dashboard | Data 3 gudang sekaligus tampil real-time |
| UAT-015 | Kepala Gudang buka dashboard | Hanya data gudang sendiri tampil |
| UAT-016 | Staf akses URL Stock Adjustment langsung | HTTP 403; akses ditolak |
| UAT-017 | Kepala Gudang export laporan ke PDF | File PDF terunduh dengan data sesuai filter |
| UAT-018 | Manajer Ops export laporan semua gudang ke Excel | File .xlsx terunduh dengan data 3 gudang |

---

## 11. Risiko Teknis & Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Server down / tidak stabil | Semua gudang tidak bisa akses sistem | Cloud provider SLA tinggi; backup harian; monitoring berkala |
| Bug kritis setelah Go-Live | Fungsi pencatatan/ROP terganggu | Testing bertahap: unit → integration → functional → system → UAT → regression |
| Keterlambatan approval stakeholder | Milestone mundur | Eskalasi maksimal 2 hari kerja; Sponsor sebagai final decision maker |
| Koneksi internet putus di salah satu gudang | Gudang tidak bisa input data | Pencatatan manual sementara; input kembali setelah koneksi pulih |
| Parameter ROP belum dikonfigurasi saat Go-Live | Sistem tidak bisa tentukan status stok | Admin wajib konfigurasi semua parameter sebelum Go-Live; sistem tampilkan "Belum Dikonfigurasi" |

---

## 12. Timeline & Milestone

| Milestone | Target | Deliverable |
|---|---|---|
| Kick-off & Tim Terbentuk | 1 Maret 2026 | Tim terbentuk; visi proyek disampaikan |
| SRS Final Disetujui | Akhir Maret 2026 | Dokumen SRS disetujui Product Owner |
| Desain & Arsitektur Selesai | Akhir April 2026 | Wireframe, prototype UI/UX, ERD disetujui |
| Development Selesai | Akhir Mei 2026 | Semua modul lolos integration testing |
| Deployment Staging | Minggu ke-1 Juni 2026 | Sistem ter-deploy di staging |
| UAT & Bug Fix Selesai | Akhir Juni 2026 | Laporan UAT; semua bug Critical/High diperbaiki |
| Final Review & Go-Live | 1–4 Juli 2026 | Sistem production aktif; serah terima |

---

*Dokumen ini disusun berdasarkan Project Charter v1.1, SRS v1.3, dan SCRUM Worksheet Sprint 1 StockPoint Jabar.*
