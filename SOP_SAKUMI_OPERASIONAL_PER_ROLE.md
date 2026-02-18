# SOP Operasional SAKUMI Per Role

## 1. Tujuan
Dokumen ini menjadi pedoman operasional standar penggunaan aplikasi SAKUMI untuk memastikan:
- pencatatan pembayaran akurat,
- cash flow jelas,
- audit trail terjaga,
- pembagian tugas tiap role konsisten.

## 2. Ruang Lingkup
SOP ini berlaku untuk role:
- Admin TU
- Bendahara
- Kepala Sekolah
- Yayasan

Modul utama yang dicakup:
- Master Data
- Invoices
- Settlements (Payments)
- Arrears Report
- Daily Report
- Monthly Report

## 3. Prinsip Akuntansi & Kontrol
1. Semua pembayaran tagihan siswa diproses melalui `Settlements`.
2. `Invoice status` adalah indikator hasil, bukan diinput manual.
3. `Daily Report` untuk cash flow hanya dari tabel pembayaran (`settlements`).
4. `Arrears Report` hanya menampilkan invoice overdue dengan outstanding > 0.
5. Pembatalan transaksi/pembayaran wajib melalui fitur `cancel` dengan alasan.
6. Dilarang melakukan edit langsung data historis di database.

## 4. Definisi Operasional
- Invoice Total: nilai tagihan awal.
- Already Paid: total alokasi pembayaran settlement yang statusnya completed.
- Outstanding: `invoice_total - already_paid`.
- Overdue: invoice dengan `due_date < tanggal hari ini`.
- Aging: umur tunggakan berdasarkan selisih hari dari due date.

## 5. SOP Per Role

## 5.1 Admin TU
### A. Tugas Harian
1. Login dan cek `Dashboard`.
2. Buka `Arrears Report` untuk melihat daftar overdue + outstanding.
3. Prioritaskan follow-up siswa dengan aging tertinggi.
4. Saat menerima pembayaran:
- buka `Arrears Report` atau `Invoice Detail`,
- klik `Pay Now` / `Create Settlement`,
- pastikan `student` dan `invoice` benar,
- isi nominal pembayaran sesuai bukti (cash/transfer/qris),
- simpan settlement.
5. Setelah simpan:
- pastikan outstanding berkurang,
- jika outstanding = 0, invoice tidak lagi muncul di arrears.
6. Akhir hari:
- cek `Daily Report` tanggal hari ini,
- cocokkan total pembayaran dengan bukti kas/bank.

### B. Tugas Mingguan
1. Bersihkan data master yang tidak valid (siswa nonaktif, kelas, kategori).
2. Rekap invoice overdue yang belum ada progres pembayaran.
3. Koordinasi dengan Bendahara atas invoice kritis (aging > 90 hari).

## 5.2 Bendahara
### A. Tugas Harian
1. Verifikasi `Daily Report` per tanggal.
2. Rekonsiliasi total pembayaran dengan mutasi bank/kas.
3. Review settlement bernilai besar atau tidak lazim.

### B. Tugas Mingguan
1. Review `Arrears Aging`:
- bucket 0-30, 31-60, 61-90, >90 hari.
2. Pastikan tidak ada overpayment.
3. Validasi pembatalan settlement beserta alasan dan pelaku.

### C. Tugas Bulanan
1. Verifikasi total pembayaran bulanan dari laporan.
2. Konfirmasi piutang outstanding akhir bulan.
3. Arsipkan laporan bulanan + export aging.

## 5.3 Kepala Sekolah
### A. Tugas Harian (Ringkas)
1. Cek ringkasan `Dashboard`.
2. Cek indikator pembayaran harian dan tunggakan utama.

### B. Tugas Mingguan
1. Review `Arrears Report` per kelas.
2. Identifikasi kelas/siswa dengan tunggakan berulang.
3. Instruksikan tindak lanjut ke Admin TU/Bendahara.

### C. Tugas Bulanan
1. Review performa pendapatan unit.
2. Review kepatuhan SOP operasional (input, cancel, audit trail).
3. Setujui rencana perbaikan penagihan bulan berikutnya.

## 5.4 Yayasan
### A. Tugas Mingguan
1. Gunakan scope `All Units` untuk monitoring lintas unit.
2. Bandingkan performa kolektibilitas antar unit.
3. Investigasi anomali (aging tinggi, mismatch cash flow).

### B. Tugas Bulanan
1. Review laporan konsolidasi:
- Monthly Report,
- Daily trend,
- Arrears Aging.
2. Minta klarifikasi unit yang memiliki outlier.
3. Tetapkan arahan kebijakan tindak lanjut.

## 6. Alur Pembayaran yang Benar
1. Pilih invoice dari `Arrears Report`/`Invoice Detail`.
2. Klik `Pay Now` ke form `Settlement`.
3. Sistem menampilkan:
- invoice total,
- already paid,
- outstanding.
4. Input nominal:
- numeric,
- minimal 1,
- maksimal outstanding.
5. Saat submit, sistem menghitung ulang outstanding dari database.
6. Jika nominal > outstanding, sistem menolak.
7. Jika valid, settlement tersimpan dan invoice ter-update otomatis.

## 7. Kontrol Audit Trail
1. Setiap settlement menyimpan:
- nomor settlement,
- tanggal pembayaran,
- metode,
- nominal,
- user pembuat.
2. Setiap pembatalan wajib:
- menggunakan fitur cancel,
- menyimpan alasan pembatalan.
3. Seluruh laporan harus dapat ditelusuri ke dokumen sumber.

## 8. SLA Operasional
1. Input pembayaran: maksimal di hari yang sama saat pembayaran diterima.
2. Rekonsiliasi harian: selesai sebelum tutup operasional.
3. Tindak lanjut tunggakan >90 hari: wajib dibahas mingguan.

## 9. Checklist Harian (Admin TU + Bendahara)
- [ ] Semua pembayaran hari ini masuk Settlement.
- [ ] Daily Report sesuai total kas/bank.
- [ ] Tidak ada settlement gagal/duplikat.
- [ ] Outstanding invoice berkurang sesuai pembayaran.
- [ ] Catatan kendala harian terdokumentasi.

## 10. Checklist Bulanan (Kepala Sekolah + Yayasan)
- [ ] Rekap pendapatan bulanan tervalidasi.
- [ ] Daftar overdue + outstanding tervalidasi.
- [ ] Aging analysis sudah dievaluasi.
- [ ] Seluruh pembatalan memiliki alasan valid.
- [ ] Export laporan diarsipkan untuk audit.

## 11. Penutup
SOP ini wajib dipatuhi oleh seluruh role terkait. Perubahan proses bisnis harus diikuti pembaruan SOP dan sosialisasi ke seluruh pengguna unit.

