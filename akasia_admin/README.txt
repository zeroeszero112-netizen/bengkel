AKASIA ADMIN

Struktur project ini dibuat khusus untuk area admin web.

Daftar direktori utama:

- assets
  Berisi file CSS dan aset tampilan.

- config
  Berisi koneksi database.

- includes
  Berisi fungsi bantu sederhana.

- layout
  Berisi header, sidebar, dan footer.

- pages
  Berisi halaman-halaman admin.

File utama:

- index.php
  Sebagai entry point dan router sederhana berdasarkan parameter page.

Halaman yang tersedia:

- dashboard 
- reservasi -> menambahkan data reservasi 
- detail_reservasi  
- status_servis-> edit antrian reservasi,seperti mengubah status pengerjaan ,lalu mengubah siapa mekanik yang mengerjakan
- mekanik 
- jenis_layanan
- kegiatan_servis
- notifikasi
- pengaturan

Integrasi database:

- Database default: akasia_motor
- Host default: 127.0.0.1
- Port default: 3306
- User default: root
- Password default: kosong

Contoh akses:
- http://localhost/akasia_admin/
- http://localhost/akasia_admin/index.php?page=reservasi
- http://localhost/akasia_admin/index.php?page=status_servis


buatkan secara detail namun simple
terapkan system hashing gunakan bootstrap admin dashboard
berikan database scriptnya agar bisa langsung berjalan di MySQL xampp ku