# Spesifikasi Sistem Informasi Web — ICM Sponsor
**Dokumen untuk Client — Bahasa Sederhana, Non-Teknis**

> Ini adalah sistem arsip digital untuk menggantikan Excel yang bertumpuk. Semua data sponsor dan kontak PIC tersimpan di satu tempat, bisa dibuka lewat browser HP / laptop di alamat `/admin`, wajib login.

---

## 1. Gambaran Singkat

**Nama sistem:** ICM Sponsor
**Fungsi utama:** Menyimpan, mencari, menambah, mengimpor, dan mengekspor data sponsor.

Ada **4 data utama** + **2 data pendukung**:

1. **Kontak** = daftar PIC / penanggung jawab perusahaan + nomor HP-nya.
2. **Perusahaan** = daftar perusahaan sponsor (nama baku, tidak kembar).
3. **Kegiatan / Event** = daftar acara yang pernah diadakan (misal PIT HOGSI XVII 2026, INDAAC JABAR 2026).
4. **Kategori Kegiatan** = pengelompokan acara (misal: Obgyn, Paru, Gizi Klinik, Anak, dll — bawaan 10 kategori + 25 event contoh sudah terisi).
5. **Histori Aktivitas** = catatan otomatis siapa mengubah apa dan kapan.
6. **Akun Pengguna** = akun Admin dan Karyawan + Profil Saya.

---

## 2. Cara Masuk & Peran Pengguna

### 2.1. Dua peran

| Peran | Bisa apa? | Cocok untuk |
|---|---|---|
| **Admin** | Semua: lihat, tambah, ubah, hapus semua data + impor + ekspor + kelola akun karyawan + lihat semua histori + menyamar sebagai karyawan untuk bantuan | Pimpinan / IT / PIC data |
| **Karyawan** | Lihat data Kontak, Perusahaan, Kegiatan, Kategori + ekspor data + ubah profil sendiri. Tidak bisa kelola akun orang lain. Histori hanya melihat milik sendiri | Tim marketing / operasional |

### 2.2. Daftar & persetujuan

1. Calon karyawan bisa daftar sendiri (nama, email, no HP, divisi, password).
2. Akun baru berstatus **nonaktif** — menunggu Admin menyetujui (Approve) / menolak (Reject).
3. Setelah disetujui baru bisa login. Ini mencegah orang luar masuk.

### 2.3. Profil Saya

Setiap orang punya halaman profil sendiri:

- Ganti foto profil (otomatis dipotong kotak/bulat, diperkecil agar ringan).
- Ubah nama, email, no HP, divisi.
- Ganti password (wajib masukkan password lama dulu agar aman).
- Jika foto rusak, otomatis tampil inisial nama (misal "BS").

---

## 3. Dashboard — Ringkasan Sekilas Setelah Login

Halaman pertama setelah login berisi:

**6 Kartu Angka:**

1. Total Perusahaan — berapa perusahaan terdaftar.
2. Total Kontak — berapa PIC terdaftar (+ berapa % nomornya valid).
3. Terverifikasi — kontak yang nomornya sudah benar.
4. Perlu Dicek — kontak baru dari impor yang belum dicek.
5. Tidak Aktif — kontak yang sudah tidak dipakai.
6. Total Kegiatan — berapa event tercatat.

**2 Grafik Batang:**

1. **Distribusi Event per Kategori** (mendatar) — kategori mana paling banyak event-nya. Warna grafik sama dengan warna label kategori.
2. **Top 5 Event Terbesar** (tegak) — 5 event dengan kontak terbanyak. Bagus untuk tahu event mana paling diminati sponsor.

> Semua angka otomatis update, tidak perlu hitung manual. Dibuat ringan agar 50 orang buka bersamaan tetap cepat.

---

## 4. Fitur Rinci per Menu

### 4.1. Menu Kontak — Lemari Arsip Utama

Ini menu paling sering dipakai. Satu baris = satu PIC.

**Isi tiap baris:**

- Nama Perusahaan (nama baku, misal "Kalbe Farma" — bukan "PT. Kalbe" / "kalbe" yang beda-beda)
- Nama PIC
- No. Telepon (bisa klik untuk salin)
- Kegiatan / Event (label berwarna, misal biru untuk Obgyn, hijau untuk Paru)
- Kategori Kegiatan (label berwarna)
- Status Verifikasi: **Hijau = Terverifikasi, Kuning = Perlu Dicek, Merah = Tidak Aktif**
- Info tambahan (tersembunyi, bisa dimunculkan): siapa terakhir mengubah, apakah nomor dipakai di perusahaan lain

**Yang bisa dilakukan:**

- **Cari cepat:** ketik nama perusahaan / nama PIC / nomor HP, hasil muncul dalam hitungan detik. Tidak perlu hafal ejaan persis.
- **Filter:** saring berdasarkan Event tertentu, Kategori tertentu, atau Status (misal hanya tampilkan yang "Perlu Dicek").
- **Tombol WhatsApp:** tiap baris ada tombol langsung chat ke nomor itu via wa.me. Tidak perlu copy-paste manual.
- **Peringatan duplikat:** kalau satu nomor HP dipakai di 2 perusahaan berbeda, barisnya diberi warna merah muda + badge peringatan (khusus Admin). Mencegah salah hubungi.
- **Warna baris otomatis:** mengikuti warna event/kategori agar mudah bedakan.
- **Lihat detail:** klik mata untuk lihat lengkap di panel geser (perusahaan, industri, event, tanggal, status, siapa pengubah, kapan dibuat).
- **Tambah / Ubah / Hapus:** form sederhana 2 kolom. Saat pilih Kegiatan, Kategorinya otomatis terisi. Saat buat/ubah, nama pengubah tercatat otomatis.
- **Jumlah per halaman:** bisa pilih 25 / 50 / 100 / 250 / 500 baris. Default 50 agar tidak berat.

**Aturan Nomor HP (penting, otomatis):**

- Sistem hanya menganggap valid kalau formatnya `628...` dengan total 10–13 digit. Contoh: `0811-1465-133` otomatis disimpan jadi `628111465133` dan ditandai valid.
- Kalau nomor aneh (kurang digit, ada huruf), **tetap disimpan apa adanya** tapi ditandai **tidak valid + status Perlu Dicek** agar bisa diperbaiki manual, tidak hilang.

### 4.2. Menu Perusahaan — Daftar Sponsor Baku

- Isi: Nama Standar (wajib, tidak boleh kembar), Bidang Industri (misal Farmasi, Alat Kesehatan), Catatan bebas, Jumlah kontak yang dimiliki, siapa terakhir mengubah.
- Fungsi: tambah perusahaan baru langsung dari sini, atau otomatis dibuat saat impor. Nama "PT Kalbe Farma Tbk", "CV Kalbe", "kalbe" otomatis disatukan jadi "Kalbe Farma" agar tidak dobel.
- Di form Kontak, bisa langsung buat perusahaan baru tanpa pindah menu.

### 4.3. Menu Kegiatan / Event

- Isi: Nama Event (misal Anxiety Master Class 2026), Kategori, Warna label, Tanggal Mulai & Selesai, Lokasi / Venue, Catatan, Jumlah kontak yang hadir dari event itu.
- Warna bisa dipilih bebas. Kalau kosong, otomatis ikut warna kategorinya.
- Kalau tanggal selesai diisi, tidak boleh lebih awal dari tanggal mulai (dicegah sistem).
- Daftar bisa disaring per Kategori, diurutkan per tanggal.

### 4.4. Menu Kategori Kegiatan

- Isi: Nama Kategori (wajib, unik), Warna, Deskripsi, Jumlah event di dalamnya.
- Bawaan sudah ada 10: Umum/Estetik, Anak, Obgyn, Gizi Klinik, Paru, Andrologi, Kejiwaan, Neurologi, Orthopedi, Multi. Bisa tambah sendiri.
- Warna di sini menentukan warna label di menu Kontak dan warna grafik dashboard.

### 4.5. Menu Histori Aktivitas

Setiap tambah / ubah / hapus tercatat otomatis:

- Kapan (tanggal + jam + "2 jam lalu")
- Siapa (nama pengguna)
- Aksi apa (Ditambahkan / Diperbarui / Dihapus)
- Data apa (Perusahaan / Kontak / Kegiatan / Kategori) + rincian sebelum vs sesudah (misal nomor lama -> nomor baru, ditandai hijau/merah)
- Admin melihat semua. Karyawan hanya melihat milik sendiri (privasi terjaga).

### 4.6. Menu Manajemen Akun (khusus Admin)

- Daftar karyawan: foto, nama, email, divisi, peran, aktif/tidak, no HP, tanggal bergabung.
- Filter berdasarkan divisi, peran, status aktif.
- Aksi: Setujui / Tolak akun baru, Ubah, Hapus, dan **Menyamar (Impersonate)** — Admin bisa masuk sementara sebagai karyawan untuk membantu tanpa minta password-nya.
- Menu Peran & Hak Akses: Admin bisa atur centang siapa boleh lihat/tambah/ubah/hapus/ekspor/impor per menu. Peran `admin` dan `karyawan` tidak bisa dihapus agar sistem tidak terkunci.

---

## 5. Impor Excel Pintar — Fitur Unggulan

Untuk memindahkan ribuan data Excel lama tanpa ketik satu-satu.

**Cara pakai (3 langkah):**

1. **Upload** file: `xlsx, xls, ods, csv, tsv`, maksimal 10 MB, tombol **Download Template** tersedia (isi contoh: nama_perusahaan, industri, nama, no_telepon, catatan).
2. **Preview otomatis:** sistem baca **semua sheet**, cari baris judul sendiri (walau judulnya beda bahasa: "Nama PIC" / "PIC" / "Contact Person" / "No HP" / "Telp" semua dikenali), tampilkan 5 baris contoh + ringkasan jumlah. Bisa saring preview per sheet / status, cari teks, bahkan **edit atau hapus baris langsung di preview** sebelum disimpan.
3. **Simpan 1 klik:** semua disimpan dalam satu transaksi aman (kalau gagal di tengah, tidak setengah-setengah).

**Kecerdasan otomatis saat impor:**

- **Tebak Event:** dari nama sheet / nama file / 8 baris atas (misal sheet "HOGSI" otomatis jadi "PIT HOGSI XVII 2026" + kategori Obgyn + tanggal + venue). Kalau ada tahun di file, otomatis ditambahkan.
- **Samakan Perusahaan:** "PT Abbott", "Abbott Indonesia" otomatis jadi "PT Abbott Indonesia". Diberi label **Baru** (belum ada) atau **Cocok** (sudah ada, tinggal tempel).
- **Cek Nomor dulu baru cek kembar:** nomor dinormalisasi ke `628...` dulu, lalu dicek apakah kembar di database atau kembar di file yang sama.
- **Buang sampah otomatis:** baris berisi kata seperti `koordinasi, serah terima, loading, vendor, doorprize, qty, checklist, spanduk, daftar, total` ditandai sampah dan tidak disimpan.
- **Pisah kontak se-sel:** kalau satu sel berisi "Budi, Ani : 0811..., 0812..." otomatis dipecah jadi 2 kontak berpasangan.
- **Hasil per kontak diberi label jelas:** `Dibuat` (siap disimpan), `Duplikat Telepon`, `Duplikat Nama`, `Duplikat di File Ini`, `Data Tidak Lengkap` (+ alasan bahasa Indonesia, misal "tanpa perusahaan", "nomor kosong", "nomor tidak lolos pola tapi tetap disimpan").
- Kontak baru otomatis berstatus **Perlu Dicek** agar diverifikasi manual.

> Sangat menghemat waktu untuk file ratusan baris yang formatnya berantakan / beda-beda.

---

## 6. Ekspor Laporan 1 Klik

- Tombol **Ekspor CSV** di atas tabel Kontak.
- **Mengikuti filter yang sedang tampil:** kalau sedang filter "Kategori Paru + Terverifikasi", yang terdownload hanya itu. Tidak perlu saring manual di Excel.
- File langsung terdownload, bisa dibuka di Microsoft Excel / LibreOffice / WPS. Sudah termasuk kode khusus (BOM) agar huruf Indonesia tidak jadi `????`.
- Isi file 8 kolom siap pakai: Nama PIC, Nama Perusahaan, Industri, No Telepon, Kegiatan, Tahun, Kategori, Status Verifikasi.

---

## 7. Keamanan & Keandalan (dijelaskan sederhana)

1. **Wajib login** — tanpa akun tidak bisa lihat data apapun.
2. **Hak akses bertingkat** — karyawan tidak bisa buka menu akun / histori orang lain.
3. **Jejak audit** — siapa mengubah apa selalu tercatat + nama pengubah tampil di tabel.
4. **Nomor otomatis divalidasi** — mengurangi salah ketik.
5. **Data lama yang hurufnya rusak** (dari Excel Windows lama) otomatis dibersihkan agar tidak jadi simbol aneh.
6. **Bisa dibuka di HP dan laptop**, tampilan menyesuaikan layar, ada mode gelap.
7. **Siap 50 pengguna bersamaan** (bagian baca) bila dipasang di server yang disarankan: MySQL + cache file, bukan SQLite untuk produksi.

---

## 8. Contoh Alur Kerja Sehari-hari

**Kasus 1 — Marketing mau undang sponsor:**
Buka Kontak -> ketik "Kalbe" di pencarian atau filter Kategori "Gizi Klinik" -> cek daftar -> klik Ekspor CSV -> file jadi bahan undangan WA.

**Kasus 2 — Ada file Excel panitia baru:**
Buka Kontak -> tombol Import Data -> upload -> cek preview (perbaiki yang merah/kuning bila perlu) -> Simpan -> data langsung masuk + perusahaan baru otomatis dibuat + kontak baru status Perlu Dicek.

**Kasus 3 — Admin tambah karyawan:**
Buka Manajemen Akun -> Tambah -> isi nama/email/divisi/peran -> karyawan daftar -> Admin Approve -> karyawan bisa login.

---

## 9. Batasan & Catatan untuk Client

- Ukuran impor maksimal **10 MB per file**. Kalau lebih, pecah jadi 2 file.
- Status verifikasi hanya ada **3 pilihan**: Terverifikasi / Perlu Dicek / Tidak Aktif. Tambah pilihan baru butuh bantuan teknisi (ubah database).
- Baris sampah (daftar belanja, checklist, total, denah booth) memang sengaja tidak disimpan.
- Kolom catatan per kontak di file impor saat ini **belum tersimpan** ke database kontak (hanya catatan perusahaan/event yang tersimpan) — perlu konfirmasi apakah mau ditambahkan.
- Aset tampilan (logo, tema) sudah jadi bawaan Filament, ganti warna besar perlu bantuan developer.
- Backup harian database + update keamanan bulanan disarankan (tugas IT hosting).

---

## 10. Ringkasan Apa yang Diterima Client

- Web siap pakai: Dashboard + 4 menu data + Impor + Ekspor + Histori + Profil + Manajemen Akun.
- Data awal: 10 kategori + 25 event + 2 akun contoh (admin & karyawan).
- Template CSV impor + panduan deploy VPS + laporan teknis 4 halaman sudah ada di folder `docs/`.
- Garansi logika: tidak ada data hilang saat impor gagal, tidak ada duplikat perusahaan karena beda penulisan PT/CV.

Jika setuju dengan pemetaan ini, langkah berikutnya yang bisa diminta ke developer: demo langsung dengan data asli 1 file Excel milik ICM + penyesuaian warna/label kategori sesuai identitas ICM.
