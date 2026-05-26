<?php
session_start();

require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/functions.php';

$page = $_GET['page'] ?? 'dashboard';
$allowedPages = [
    'login',
    'dashboard',
    'reservasi',
    'detail_reservasi',
    'status_servis',
    'mekanik',
    'jenis_layanan',
    'kegiatan_servis',
    'notifikasi',
    'pengaturan',
    'template_notifikasi',
    'riwayat_servis',
];

if (!in_array($page, $allowedPages, true)) {
    $page = 'dashboard';
}

function findReservasi(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("
        SELECT r.*, u.nama AS pelanggan, u.no_whatsapp, jl.nama AS layanan, m.nama AS mekanik_nama
        FROM reservasi r
        INNER JOIN users u ON u.id = r.user_id
        INNER JOIN jenis_layanan jl ON jl.id = r.jenis_layanan_id
        LEFT JOIN mekanik m ON m.id = r.mekanik_id
        WHERE r.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);

    return $stmt->fetch() ?: null;
}

function updateMekanikStatus(PDO $pdo, ?int $mekanikId, string $status): void
{
    if (!$mekanikId) {
        return;
    }

    $stmt = $pdo->prepare("UPDATE mekanik SET status = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $status,
        ':id' => $mekanikId,
    ]);
}

function formatExceptionMessage(Throwable $e): string
{
    $message = trim($e->getMessage());

    if ($message === '') {
        return 'Terjadi kesalahan sistem.';
    }

    return $message;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("
            SELECT id, nama, email, password, role
            FROM users
            WHERE email = :email AND role IN ('admin', 'owner')
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin'] = [
                'id' => (int) $admin['id'],
                'nama' => $admin['nama'],
                'email' => $admin['email'],
                'role' => $admin['role'],
            ];

            setFlash('success', 'Login berhasil.');
            redirectTo('index.php?page=dashboard');
        }

        setFlash('danger', 'Email atau password tidak valid.');
        redirectTo('index.php?page=login');
    }

    requireAdminAuth();

    if ($action === 'save_walkin') {
        $nama = trim($_POST['nama'] ?? '');
        $noWhatsapp = normalizePhone($_POST['no_whatsapp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $noPlat = trim($_POST['no_plat'] ?? '');
        $jenisKendaraan = trim($_POST['jenis_kendaraan'] ?? '');
        $tipeModel = trim($_POST['tipe_model'] ?? '');
        $tahun = trim($_POST['tahun'] ?? '');
        $warna = trim($_POST['warna'] ?? '');
        $keluhan = trim($_POST['keluhan'] ?? '');
        $jenisLayananId = (int) ($_POST['jenis_layanan_id'] ?? 0);
        $tanggalServis = trim($_POST['tanggal_servis'] ?? date('Y-m-d'));
        $kegiatanIds = $_POST['kegiatan_ids'] ?? [];

        if ($nama === '' || $noPlat === '' || $jenisLayananId <= 0 || !isValidDateValue($tanggalServis)) {
            setFlash('danger', 'Data reservasi walk-in belum lengkap.');
            redirectTo('index.php?page=reservasi&form=walkin');
        }

        if (!isValidPhone($noWhatsapp)) {
            setFlash('danger', 'Nomor WhatsApp pelanggan tidak valid.');
            redirectTo('index.php?page=reservasi&form=walkin');
        }

        $pdo->beginTransaction();

        try {
            $userStmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE role = 'pelanggan'
                  AND nama = :nama
                  AND COALESCE(no_whatsapp, '') = :no_whatsapp
                LIMIT 1
            ");
            $userStmt->execute([
                ':nama' => $nama,
                ':no_whatsapp' => $noWhatsapp,
            ]);
            $existingUser = $userStmt->fetch();

            if ($existingUser) {
                $userId = (int) $existingUser['id'];
                $updateUserStmt = $pdo->prepare("UPDATE users SET alamat = :alamat WHERE id = :id");
                $updateUserStmt->execute([
                    ':alamat' => $alamat !== '' ? $alamat : null,
                    ':id' => $userId,
                ]);
            } else {
                $insertUserStmt = $pdo->prepare("
                    INSERT INTO users (nama, email, password, no_whatsapp, alamat, role)
                    VALUES (:nama, :email, :password, :no_whatsapp, :alamat, 'pelanggan')
                ");
                $insertUserStmt->execute([
                    ':nama' => $nama,
                    ':email' => generatePelangganEmail($pdo, $nama, $noWhatsapp),
                    ':password' => password_hash('walkin123', PASSWORD_DEFAULT),
                    ':no_whatsapp' => $noWhatsapp !== '' ? $noWhatsapp : null,
                    ':alamat' => $alamat !== '' ? $alamat : null,
                ]);
                $userId = (int) $pdo->lastInsertId();
            }

            $noAntrian = generateQueueNumber($pdo, $tanggalServis);

            $insertReservasiStmt = $pdo->prepare("
                INSERT INTO reservasi (
                    no_antrian, user_id, mekanik_id, jenis_layanan_id, jenis_reservasi,
                    no_plat, jenis_kendaraan, tipe_model, tahun, warna, keluhan,
                    kehadiran, status, tanggal_servis
                ) VALUES (
                    :no_antrian, :user_id, NULL, :jenis_layanan_id, 'Walk-in',
                    :no_plat, :jenis_kendaraan, :tipe_model, :tahun, :warna, :keluhan,
                    'Belum Hadir', 'menunggu_konfirmasi', :tanggal_servis
                )
            ");
            $insertReservasiStmt->execute([
                ':no_antrian' => $noAntrian,
                ':user_id' => $userId,
                ':jenis_layanan_id' => $jenisLayananId,
                ':no_plat' => $noPlat,
                ':jenis_kendaraan' => $jenisKendaraan !== '' ? $jenisKendaraan : null,
                ':tipe_model' => $tipeModel !== '' ? $tipeModel : null,
                ':tahun' => $tahun !== '' ? (int) $tahun : null,
                ':warna' => $warna !== '' ? $warna : null,
                ':keluhan' => $keluhan !== '' ? $keluhan : null,
                ':tanggal_servis' => $tanggalServis,
            ]);

            $reservasiId = (int) $pdo->lastInsertId();

            if (is_array($kegiatanIds)) {
                $insertKegiatanStmt = $pdo->prepare("
                    INSERT INTO reservasi_kegiatan (reservasi_id, kegiatan_servis_id)
                    VALUES (:reservasi_id, :kegiatan_servis_id)
                ");

                foreach ($kegiatanIds as $kegiatanId) {
                    $kegiatanId = (int) $kegiatanId;
                    if ($kegiatanId > 0) {
                        $insertKegiatanStmt->execute([
                            ':reservasi_id' => $reservasiId,
                            ':kegiatan_servis_id' => $kegiatanId,
                        ]);
                    }
                }
            }

            $pdo->commit();
            setFlash('success', 'Reservasi walk-in berhasil dibuat dengan nomor antrean ' . $noAntrian . '.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            setFlash('danger', 'Reservasi walk-in gagal disimpan. Penyebab: ' . formatExceptionMessage($e));
            redirectTo('index.php?page=reservasi&form=walkin');
        }

        redirectTo('index.php?page=reservasi');
    }

    if ($action === 'confirm_reservasi' || $action === 'cancel_reservasi' || $action === 'confirm_kehadiran' || $action === 'mark_pending' || $action === 'callback_pending') {
        $id = (int) ($_POST['id'] ?? 0);
        $reservasi = findReservasi($pdo, $id);

        if (!$reservasi) {
            setFlash('danger', 'Data reservasi tidak ditemukan.');
            redirectTo('index.php?page=status_servis');
        }

        $pdo->beginTransaction();

        try {
            if ($action === 'confirm_reservasi') {
                $stmt = $pdo->prepare("UPDATE reservasi SET status = 'dikonfirmasi', waktu_konfirmasi = NOW() WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $reservasi['status'] = 'dikonfirmasi';
                $reservasi['id'] = $id;
                buildAndSendTemplate($pdo, $reservasi, 'reservasi_dikonfirmasi', 'Konfirmasi Reservasi', [
                    'nama' => $reservasi['pelanggan'],
                    'antrean' => $reservasi['no_antrian'],
                    'tanggal' => formatDateIndonesia($reservasi['tanggal_servis']),
                ]);
            }

            if ($action === 'cancel_reservasi') {
                if (!empty($reservasi['mekanik_id'])) {
                    updateMekanikStatus($pdo, (int) $reservasi['mekanik_id'], 'tersedia');
                }
                $stmt = $pdo->prepare("UPDATE reservasi SET status = 'dibatalkan' WHERE id = :id");
                $stmt->execute([':id' => $id]);
                buildAndSendTemplate($pdo, $reservasi, 'reservasi_dibatalkan', 'Perubahan Status', [
                    'nama' => $reservasi['pelanggan'],
                    'antrean' => $reservasi['no_antrian'],
                ]);
            }

            if ($action === 'confirm_kehadiran') {
                $stmt = $pdo->prepare("
                    UPDATE reservasi
                    SET status = 'menunggu_antrean', kehadiran = 'Hadir', waktu_hadir = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([':id' => $id]);
                buildAndSendTemplate($pdo, $reservasi, 'kehadiran_dikonfirmasi', 'Perubahan Status', [
                    'nama' => $reservasi['pelanggan'],
                ]);
            }

            if ($action === 'mark_pending') {
                $stmt = $pdo->prepare("
                    UPDATE reservasi
                    SET status = 'pending', kehadiran = 'Belum Hadir', waktu_pending = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([':id' => $id]);
                buildAndSendTemplate($pdo, $reservasi, 'pending_kehadiran', 'Callback Pending', [
                    'nama' => $reservasi['pelanggan'],
                    'antrean' => $reservasi['no_antrian'],
                ]);
            }

            if ($action === 'callback_pending') {
                $stmt = $pdo->prepare("UPDATE reservasi SET status = 'menunggu_antrean' WHERE id = :id");
                $stmt->execute([':id' => $id]);
                buildAndSendTemplate($pdo, $reservasi, 'callback_antrean', 'Callback Pending', [
                    'nama' => $reservasi['pelanggan'],
                ]);
            }

            $pdo->commit();
            setFlash('success', 'Perubahan data berhasil disimpan.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            setFlash('danger', 'Perubahan data gagal disimpan. Penyebab: ' . formatExceptionMessage($e));
        }

        redirectTo($_POST['redirect_to'] ?? 'index.php?page=status_servis');
    }

    if ($action === 'start_service') {
        $id = (int) ($_POST['id'] ?? 0);
        $mekanikId = (int) ($_POST['mekanik_id'] ?? 0);
        $reservasi = findReservasi($pdo, $id);

        if (!$reservasi || $mekanikId <= 0) {
            setFlash('danger', 'Data proses servis belum lengkap.');
            redirectTo('index.php?page=status_servis&stage=menunggu_antrean');
        }

        $pdo->beginTransaction();

        try {
            $mekanikStmt = $pdo->prepare("SELECT id, nama FROM mekanik WHERE id = :id AND status = 'tersedia' LIMIT 1");
            $mekanikStmt->execute([':id' => $mekanikId]);
            $mekanik = $mekanikStmt->fetch();

            if (!$mekanik) {
                throw new RuntimeException('Mekanik tidak tersedia.');
            }

            $stmt = $pdo->prepare("
                UPDATE reservasi
                SET status = 'diproses',
                    mekanik_id = :mekanik_id,
                    waktu_mulai_servis = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':mekanik_id' => $mekanikId,
                ':id' => $id,
            ]);

            updateMekanikStatus($pdo, $mekanikId, 'sibuk');

            $reservasi['id'] = $id;
            $reservasi['no_whatsapp'] = $reservasi['no_whatsapp'] ?? '';
            buildAndSendTemplate($pdo, $reservasi, 'proses_servis_dimulai', 'Perubahan Status', [
                'nama' => $reservasi['pelanggan'],
                'antrean' => $reservasi['no_antrian'],
                'mekanik' => $mekanik['nama'],
            ]);

            $pdo->commit();
            setFlash('success', 'Servis berhasil dimulai.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            setFlash('danger', 'Servis gagal dimulai. Penyebab: ' . formatExceptionMessage($e));
        }

        redirectTo('index.php?page=status_servis&stage=menunggu_antrean');
    }

    if ($action === 'complete_service') {
        $id = (int) ($_POST['id'] ?? 0);
        $hasilServis = trim($_POST['hasil_servis'] ?? '');
        $biayaJasa = (float) ($_POST['biaya_jasa'] ?? 0);
        $catatanTambahan = trim($_POST['catatan_tambahan'] ?? '');
        $sparepartNames = $_POST['sparepart_nama'] ?? [];
        $sparepartQty = $_POST['sparepart_qty'] ?? [];
        $sparepartHarga = $_POST['sparepart_harga'] ?? [];
        $reservasi = findReservasi($pdo, $id);

        if (!$reservasi) {
            setFlash('danger', 'Data servis tidak ditemukan.');
            redirectTo('index.php?page=status_servis&stage=diproses');
        }

        $totalSparepart = 0;
        $spareparts = [];
        foreach ($sparepartNames as $index => $name) {
            $name = trim((string) $name);
            $qty = max(1, (int) ($sparepartQty[$index] ?? 1));
            $harga = (float) ($sparepartHarga[$index] ?? 0);

            if ($name === '') {
                continue;
            }

            $subtotal = $qty * $harga;
            $totalSparepart += $subtotal;
            $spareparts[] = [
                'nama' => $name,
                'qty' => $qty,
                'harga' => $harga,
                'subtotal' => $subtotal,
            ];
        }

        $totalBiaya = $biayaJasa + $totalSparepart;

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                UPDATE reservasi
                SET status = 'selesai',
                    hasil_servis = :hasil_servis,
                    biaya_jasa = :biaya_jasa,
                    total_biaya = :total_biaya,
                    catatan_tambahan = :catatan_tambahan,
                    waktu_selesai = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                ':hasil_servis' => $hasilServis !== '' ? $hasilServis : null,
                ':biaya_jasa' => $biayaJasa,
                ':total_biaya' => $totalBiaya,
                ':catatan_tambahan' => $catatanTambahan !== '' ? $catatanTambahan : null,
                ':id' => $id,
            ]);

            $deleteStmt = $pdo->prepare("DELETE FROM reservasi_sparepart WHERE reservasi_id = :reservasi_id");
            $deleteStmt->execute([':reservasi_id' => $id]);

            if ($spareparts) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO reservasi_sparepart (reservasi_id, nama_item, qty, harga, subtotal)
                    VALUES (:reservasi_id, :nama_item, :qty, :harga, :subtotal)
                ");

                foreach ($spareparts as $item) {
                    $insertStmt->execute([
                        ':reservasi_id' => $id,
                        ':nama_item' => $item['nama'],
                        ':qty' => $item['qty'],
                        ':harga' => $item['harga'],
                        ':subtotal' => $item['subtotal'],
                    ]);
                }
            }

            updateMekanikStatus($pdo, $reservasi['mekanik_id'] ? (int) $reservasi['mekanik_id'] : null, 'tersedia');

            buildAndSendTemplate($pdo, $reservasi, 'servis_selesai', 'Servis Selesai', [
                'nama' => $reservasi['pelanggan'],
                'antrean' => $reservasi['no_antrian'],
                'total' => number_format($totalBiaya, 0, ',', '.'),
            ]);

            $pdo->commit();
            setFlash('success', 'Data penyelesaian servis berhasil disimpan.');
        } catch (Throwable $e) {
            $pdo->rollBack();
            setFlash('danger', 'Data penyelesaian servis gagal disimpan. Penyebab: ' . formatExceptionMessage($e));
        }

        redirectTo('index.php?page=status_servis&stage=selesai');
    }

    if ($action === 'save_mekanik') {
        $id = (int) ($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $noHp = normalizePhone($_POST['no_hp'] ?? '');
        $status = $_POST['status_mekanik'] ?? 'tersedia';

        if ($nama === '' || !isValidPhone($noHp)) {
            setFlash('danger', 'Data mekanik belum valid.');
            redirectTo('index.php?page=mekanik');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE mekanik SET nama = :nama, no_hp = :no_hp, status = :status WHERE id = :id");
            $stmt->execute([
                ':nama' => $nama,
                ':no_hp' => $noHp !== '' ? $noHp : null,
                ':status' => $status,
                ':id' => $id,
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO mekanik (nama, no_hp, status) VALUES (:nama, :no_hp, :status)");
            $stmt->execute([
                ':nama' => $nama,
                ':no_hp' => $noHp !== '' ? $noHp : null,
                ':status' => $status,
            ]);
        }

        setFlash('success', 'Data mekanik berhasil disimpan.');
        redirectTo('index.php?page=mekanik');
    }

    if ($action === 'delete_mekanik') {
        $stmt = $pdo->prepare("DELETE FROM mekanik WHERE id = :id");
        $stmt->execute([':id' => (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Data mekanik berhasil dihapus.');
        redirectTo('index.php?page=mekanik');
    }

    if ($action === 'save_jenis_layanan') {
        $id = (int) ($_POST['id'] ?? 0);
        $nama = trim($_POST['nama'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $harga = (int) ($_POST['harga'] ?? 0);

        if ($nama === '') {
            setFlash('danger', 'Nama jenis layanan wajib diisi.');
            redirectTo('index.php?page=jenis_layanan');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE jenis_layanan SET nama = :nama, deskripsi = :deskripsi, harga = :harga WHERE id = :id");
            $stmt->execute([
                ':nama' => $nama,
                ':deskripsi' => $deskripsi !== '' ? $deskripsi : null,
                ':harga' => $harga,
                ':id' => $id,
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO jenis_layanan (nama, deskripsi, harga) VALUES (:nama, :deskripsi, :harga)");
            $stmt->execute([
                ':nama' => $nama,
                ':deskripsi' => $deskripsi !== '' ? $deskripsi : null,
                ':harga' => $harga,
            ]);
        }

        setFlash('success', 'Jenis layanan berhasil disimpan.');
        redirectTo('index.php?page=jenis_layanan');
    }

    if ($action === 'delete_jenis_layanan') {
        $stmt = $pdo->prepare("DELETE FROM jenis_layanan WHERE id = :id");
        $stmt->execute([':id' => (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Jenis layanan berhasil dihapus.');
        redirectTo('index.php?page=jenis_layanan');
    }

    if ($action === 'save_kegiatan_servis') {
        $id = (int) ($_POST['id'] ?? 0);
        $jenisLayananId = (int) ($_POST['jenis_layanan_id'] ?? 0);
        $namaKegiatan = trim($_POST['nama_kegiatan'] ?? '');

        if ($jenisLayananId <= 0 || $namaKegiatan === '') {
            setFlash('danger', 'Data kegiatan servis wajib dilengkapi.');
            redirectTo('index.php?page=kegiatan_servis');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE kegiatan_servis SET jenis_layanan_id = :jenis_layanan_id, nama_kegiatan = :nama_kegiatan WHERE id = :id");
            $stmt->execute([
                ':jenis_layanan_id' => $jenisLayananId,
                ':nama_kegiatan' => $namaKegiatan,
                ':id' => $id,
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO kegiatan_servis (jenis_layanan_id, nama_kegiatan) VALUES (:jenis_layanan_id, :nama_kegiatan)");
            $stmt->execute([
                ':jenis_layanan_id' => $jenisLayananId,
                ':nama_kegiatan' => $namaKegiatan,
            ]);
        }

        setFlash('success', 'Kegiatan servis berhasil disimpan.');
        redirectTo('index.php?page=kegiatan_servis');
    }

    if ($action === 'delete_kegiatan_servis') {
        $stmt = $pdo->prepare("DELETE FROM kegiatan_servis WHERE id = :id");
        $stmt->execute([':id' => (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Kegiatan servis berhasil dihapus.');
        redirectTo('index.php?page=kegiatan_servis');
    }

    if ($action === 'update_pengaturan') {
        $id = (int) ($_POST['id'] ?? 0);
        $noWhatsapp = normalizePhone($_POST['no_whatsapp'] ?? '');

        if (!isValidPhone($noWhatsapp)) {
            setFlash('danger', 'Nomor WhatsApp bengkel tidak valid.');
            redirectTo('index.php?page=pengaturan');
        }

        $stmt = $pdo->prepare("
            UPDATE pengaturan
            SET nama_bengkel = :nama_bengkel,
                alamat = :alamat,
                no_whatsapp = :no_whatsapp,
                jam_buka = :jam_buka,
                jam_tutup = :jam_tutup,
                hari_operasional = :hari_operasional
            WHERE id = :id
        ");
        $stmt->execute([
            ':nama_bengkel' => trim($_POST['nama_bengkel'] ?? ''),
            ':alamat' => trim($_POST['alamat'] ?? ''),
            ':no_whatsapp' => $noWhatsapp !== '' ? $noWhatsapp : null,
            ':jam_buka' => $_POST['jam_buka'] ?? null,
            ':jam_tutup' => $_POST['jam_tutup'] ?? null,
            ':hari_operasional' => trim($_POST['hari_operasional'] ?? ''),
            ':id' => $id,
        ]);

        setFlash('success', 'Pengaturan bengkel berhasil diperbarui.');
        redirectTo('index.php?page=pengaturan');
    }

    if ($action === 'save_template') {
        $id = (int) ($_POST['id'] ?? 0);
        $namTemplate = trim($_POST['nama_template'] ?? '');
        $isiPesan = trim($_POST['isi_pesan'] ?? '');
        $isActive = (int) ($_POST['is_active'] ?? 1);

        if ($namTemplate === '' || $isiPesan === '') {
            setFlash('danger', 'Nama template dan isi pesan wajib diisi.');
            redirectTo('index.php?page=template_notifikasi&edit=' . $id);
        }

        $stmt = $pdo->prepare("UPDATE template_whatsapp SET nama_template = :nama_template, isi_pesan = :isi_pesan, is_active = :is_active WHERE id = :id");
        $stmt->execute([
            ':nama_template' => $namTemplate,
            ':isi_pesan'     => $isiPesan,
            ':is_active'     => $isActive,
            ':id'            => $id,
        ]);

        setFlash('success', 'Template notifikasi berhasil disimpan.');
        redirectTo('index.php?page=template_notifikasi');
    }
}

if ($page === 'login' && isAdminLoggedIn()) {
    redirectTo('index.php?page=dashboard');
}

if ($page === 'reservasi') {
    $query = $_GET;
    unset($query['page']);
    $query['page'] = 'status_servis';
    $query['stage'] = 'menunggu_konfirmasi';
    redirectTo(buildPageUrl($query));
}

$pageFile = __DIR__ . '/pages/' . $page . '.php';
if (!file_exists($pageFile)) {
    $page = 'dashboard';
    $pageFile = __DIR__ . '/pages/dashboard.php';
}

$pageTitleMap = [
    'login' => 'Login Admin',
    'dashboard' => 'Dashboard Admin',
    'reservasi' => 'Menunggu Konfirmasi',
    'detail_reservasi' => 'Detail Reservasi',
    'status_servis' => 'Kelola Reservasi & Antrean',
    'mekanik' => 'Data Mekanik',
    'jenis_layanan' => 'Jenis Layanan',
    'kegiatan_servis' => 'Kegiatan Servis',
    'notifikasi' => 'Notifikasi WhatsApp',
    'pengaturan' => 'Pengaturan',
    'template_notifikasi' => 'Template Notifikasi WhatsApp',
    'riwayat_servis' => 'Riwayat Servis',
];

$pageTitle = $pageTitleMap[$page] ?? 'Akasia Motor Admin';
$flash = getFlash();
$adminUser = currentAdmin();

if ($page !== 'login') {
    requireAdminAuth();
}

if ($page === 'login') {
    require $pageFile;
    exit;
}

require __DIR__ . '/layout/header.php';
require __DIR__ . '/layout/sidebar.php';
require $pageFile;
require __DIR__ . '/layout/footer.php';
