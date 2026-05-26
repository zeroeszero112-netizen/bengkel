<?php
$search = trim($_GET['q'] ?? '');
$stage = trim($_GET['stage'] ?? 'dikonfirmasi');
$stages = getReservasiStages();
if (!isset($stages[$stage])) {
    $stage = 'dikonfirmasi';
}
$filterMode = trim($_GET['mode'] ?? 'hari_ini');
$showWalkinForm = ($_GET['form'] ?? '') === 'walkin';

$pageSize = 8;
$currentPage = max(1, (int) ($_GET['p'] ?? 1));
$whereSql = "WHERE r.status = :status";
$params = [':status' => $stage];

if ($stage === 'menunggu_konfirmasi' && $filterMode === 'hari_ini') {
    $whereSql .= " AND r.tanggal_servis = CURDATE()";
}

if ($search !== '') {
    $whereSql .= " AND (u.nama LIKE :search OR r.no_antrian LIKE :search OR r.no_plat LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$countStmt = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM reservasi r
    INNER JOIN users u ON u.id = r.user_id
    $whereSql
");
$countStmt->execute($params);
$totalRows = (int) ($countStmt->fetch()['total'] ?? 0);
$totalPages = max(1, (int) ceil($totalRows / $pageSize));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $pageSize;

$stmt = $pdo->prepare("
    SELECT
        r.*,
        u.nama AS pelanggan,
        u.no_whatsapp,
        jl.nama AS layanan,
        jl.harga AS harga_layanan,
        m.nama AS mekanik_nama
    FROM reservasi r
    INNER JOIN users u ON u.id = r.user_id
    INNER JOIN jenis_layanan jl ON jl.id = r.jenis_layanan_id
    LEFT JOIN mekanik m ON m.id = r.mekanik_id
    $whereSql
    ORDER BY COALESCE(r.waktu_pending, r.waktu_mulai_servis, r.waktu_hadir, r.waktu_konfirmasi, r.created_at) ASC, r.id ASC
    LIMIT " . (int) $pageSize . " OFFSET " . (int) $offset
);
$stmt->execute($params);
$reservasiList = $stmt->fetchAll();

$selectedId = (int) ($_GET['id'] ?? ($reservasiList[0]['id'] ?? 0));
$selected = null;
foreach ($reservasiList as $item) {
    if ((int) $item['id'] === $selectedId) {
        $selected = $item;
        break;
    }
}
if (!$selected && $reservasiList) {
    $selected = $reservasiList[0];
}

$mekanikStmt = $pdo->query("SELECT id, nama, status FROM mekanik WHERE status = 'tersedia' ORDER BY nama ASC");
$mekanikList = $mekanikStmt->fetchAll();

$jenisLayananStmt = $pdo->query("SELECT id, nama, harga FROM jenis_layanan ORDER BY id ASC");
$jenisLayananList = $jenisLayananStmt->fetchAll();
$kegiatanStmt = $pdo->query("SELECT id, nama_kegiatan, jenis_layanan_id FROM kegiatan_servis ORDER BY jenis_layanan_id ASC, nama_kegiatan ASC");
$kegiatanServisList = $kegiatanStmt->fetchAll();

$sparepartList = [];
if ($selected && $stage === 'selesai') {
    $sparepartStmt = $pdo->prepare("SELECT nama_item, qty, harga, subtotal FROM reservasi_sparepart WHERE reservasi_id = :reservasi_id ORDER BY id ASC");
    $sparepartStmt->execute([':reservasi_id' => $selected['id']]);
    $sparepartList = $sparepartStmt->fetchAll();
}

function stageTimeLabel(string $stage): string
{
    return match ($stage) {
        'pending' => 'Waktu Pending',
        'diproses' => 'Waktu Proses',
        'selesai' => 'Tanggal Selesai',
        default => 'Tanggal Servis',
    };
}

function stageTimeValue(array $item, string $stage): string
{
    return match ($stage) {
        'dikonfirmasi' => formatDateIndonesia($item['tanggal_servis']),
        'menunggu_antrean' => formatDateIndonesia($item['tanggal_servis']),
        'pending' => formatDateTimeIndonesia($item['waktu_pending'] ?: $item['updated_at']),
        'diproses' => formatDateTimeIndonesia($item['waktu_mulai_servis'] ?: $item['updated_at']),
        'selesai' => formatDateTimeIndonesia($item['waktu_selesai'] ?: $item['updated_at']),
        default => formatDateIndonesia($item['tanggal_servis']),
    };
}
?>

<div class="card">
    <div class="card-body">
        <div class="toolbar">
            <div class="toolbar-group">
                <?php foreach ($stages as $stageKey => $meta): ?>
                    <a href="index.php?page=status_servis&stage=<?= urlencode($stageKey) ?>" class="stage-chip <?= $stage === $stageKey ? 'active' : '' ?>">
                        <i class="bi <?= htmlspecialchars($meta['icon']) ?>"></i>
                        <?= htmlspecialchars($meta['label']) ?>
                    </a>
                <?php endforeach; ?>
                <?php if ($stage === 'menunggu_konfirmasi'): ?>
                    <a href="index.php?page=status_servis&stage=menunggu_konfirmasi&mode=hari_ini" class="stage-chip <?= $filterMode === 'hari_ini' ? 'active' : '' ?>">Hari Ini</a>
                    <a href="index.php?page=status_servis&stage=menunggu_konfirmasi&mode=semua" class="stage-chip <?= $filterMode === 'semua' ? 'active' : '' ?>">Semua Reservasi</a>
                    <a href="index.php?page=status_servis&stage=menunggu_konfirmasi&form=walkin" class="btn btn-primary">Reservasi Walk-in</a>
                <?php endif; ?>
            </div>
            <form method="get" action="index.php" class="toolbar-group">
                <input type="hidden" name="page" value="status_servis">
                <input type="hidden" name="stage" value="<?= htmlspecialchars($stage) ?>">
                <?php if ($stage === 'menunggu_konfirmasi'): ?><input type="hidden" name="mode" value="<?= htmlspecialchars($filterMode) ?>"><?php endif; ?>
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Cari pelanggan / nomor antrean / kendaraan">
            </form>
        </div>

        <?php if ($showWalkinForm && $stage === 'menunggu_konfirmasi'): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="section-title">Form Reservasi Walk-in</div>
                    <form method="post" action="index.php?page=status_servis&stage=menunggu_konfirmasi">
                        <input type="hidden" name="action" value="save_walkin">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Nama Pelanggan</label><input type="text" name="nama" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label">Nomor WhatsApp</label><input type="text" name="no_whatsapp" class="form-control"><div class="form-text">Format nomor aktif pelanggan, contoh: 62812xxxxxxx.</div></div>
                            <div class="col-md-4"><label class="form-label">Tanggal Servis</label><input type="date" name="tanggal_servis" class="form-control" value="<?= htmlspecialchars(date('Y-m-d')) ?>" required></div>
                            <div class="col-12"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" rows="2"></textarea></div>
                            <div class="col-md-3"><label class="form-label">Nomor Plat</label><input type="text" name="no_plat" class="form-control" required></div>
                            <div class="col-md-3"><label class="form-label">Jenis Kendaraan</label><input type="text" name="jenis_kendaraan" class="form-control"></div>
                            <div class="col-md-3"><label class="form-label">Tipe Model</label><input type="text" name="tipe_model" class="form-control"></div>
                            <div class="col-md-3"><label class="form-label">Tahun</label><input type="number" name="tahun" class="form-control" min="1990" max="2099"></div>
                            <div class="col-md-4"><label class="form-label">Warna</label><input type="text" name="warna" class="form-control"></div>
                            <div class="col-md-8"><label class="form-label">Jenis Servis</label><select name="jenis_layanan_id" class="form-select layanan-selector" data-target="#walkin-kegiatan-list" required><option value="">Pilih Jenis Servis</option><?php foreach ($jenisLayananList as $layanan): ?><option value="<?= (int) $layanan['id'] ?>"><?= htmlspecialchars($layanan['nama']) ?> - Rp <?= number_format((int) $layanan['harga'], 0, ',', '.') ?></option><?php endforeach; ?></select></div>
                            <div class="col-12"><label class="form-label">Keluhan</label><textarea name="keluhan" class="form-control" rows="3"></textarea></div>
                            <div class="col-12">
                                <label class="form-label">Checklist Kegiatan</label>
                                <div class="row g-3" id="walkin-kegiatan-list">
                                    <?php foreach ($jenisLayananList as $layanan): ?>
                                        <div class="col-12 col-xl-4 kegiatan-group" data-layanan-id="<?= (int) $layanan['id'] ?>">
                                            <div class="checklist-box">
                                                <div class="fw-semibold mb-2"><?= htmlspecialchars($layanan['nama']) ?></div>
                                                <?php foreach ($kegiatanServisList as $kegiatan): ?>
                                                    <?php if ((int) $kegiatan['jenis_layanan_id'] === (int) $layanan['id']): ?>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" name="kegiatan_ids[]" value="<?= (int) $kegiatan['id'] ?>" id="walkin_<?= (int) $kegiatan['id'] ?>">
                                                            <label class="form-check-label" for="walkin_<?= (int) $kegiatan['id'] ?>"><?= htmlspecialchars($kegiatan['nama_kegiatan']) ?></label>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Reservasi</button>
                                <a href="index.php?page=status_servis&stage=menunggu_konfirmasi" class="btn btn-outline-secondary">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="data-shell">
            <div class="card">
                <div class="card-body">
                    <div class="section-title"><?= htmlspecialchars($stages[$stage]['label']) ?></div>
                    <?php if ($stage === 'diproses'): ?>
                        <div class="process-list">
                            <?php foreach ($reservasiList as $item): ?>
                                <div class="process-item">
                                    <div class="process-meta">
                                        <div>
                                            <span class="process-code"><?= htmlspecialchars($item['no_antrian']) ?></span>
                                            <div class="fw-semibold fs-5"><?= htmlspecialchars($item['pelanggan']) ?></div>
                                        </div>
                                        <div class="process-line"><i class="bi bi-whatsapp"></i><span><?= htmlspecialchars(displayPhone($item['no_whatsapp'])) ?></span></div>
                                    </div>
                                    <div class="process-meta">
                                        <div class="process-line"><i class="bi bi-bicycle"></i><span><?= htmlspecialchars($item['tipe_model'] ?: '-') ?></span></div>
                                        <div class="process-line"><i class="bi bi-postcard"></i><span><?= htmlspecialchars($item['no_plat']) ?></span></div>
                                        <div class="process-line"><i class="bi bi-tag"></i><span><?= htmlspecialchars($item['layanan']) ?></span></div>
                                    </div>
                                    <div class="process-meta">
                                        <div class="process-line"><i class="bi bi-person-gear"></i><span><?= htmlspecialchars($item['mekanik_nama'] ?: '-') ?></span></div>
                                        <div class="process-line"><i class="bi bi-clock"></i><span><?= htmlspecialchars(stageTimeValue($item, $stage)) ?></span></div>
                                        <div><span class="badge <?= getStatusBadgeClass($item['status']) ?>"><?= htmlspecialchars($stages[$stage]['label']) ?></span></div>
                                    </div>
                                    <div class="process-actions">
                                        <a href="index.php?page=status_servis&stage=<?= urlencode($stage) ?>&id=<?= (int) $item['id'] ?>&q=<?= urlencode($search) ?>" class="btn btn-outline-primary">Detail</a>
                                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#selesaiModal<?= (int) $item['id'] ?>">Selesaikan</button>
                                    </div>
                                </div>

                                <div class="modal fade" id="selesaiModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-body p-4">
                                        <h5 class="mb-3">Penyelesaian Servis</h5>
                                        <form method="post" action="index.php?page=status_servis&stage=diproses">
                                            <input type="hidden" name="action" value="complete_service">
                                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">Hasil Servis</label>
                                                    <textarea name="hasil_servis" class="form-control" rows="3" required></textarea>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Biaya Jasa</label>
                                                    <input type="number" name="biaya_jasa" class="form-control js-biaya-jasa" min="0" value="<?= (int) $item['harga_layanan'] ?>">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Sparepart Manual</label>
                                                    <div class="table-responsive">
                                                        <table class="table sparepart-table mb-2" data-sparepart-table>
                                                            <thead>
                                                                <tr><th>Nama Item</th><th>Qty</th><th>Harga</th><th>Subtotal</th><th></th></tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td><input type="text" name="sparepart_nama[]" class="form-control"></td>
                                                                    <td><input type="number" name="sparepart_qty[]" class="form-control js-sparepart-qty" value="1" min="1"></td>
                                                                    <td><input type="number" name="sparepart_harga[]" class="form-control js-sparepart-harga" value="0" min="0"></td>
                                                                    <td><input type="text" class="form-control js-sparepart-subtotal" value="0" readonly></td>
                                                                    <td><button type="button" class="btn btn-outline-danger btn-sm js-remove-item">Hapus</button></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-primary btn-sm js-add-sparepart">+ Tambah Item</button>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Catatan Tambahan</label>
                                                    <textarea name="catatan_tambahan" class="form-control" rows="2"></textarea>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Total Biaya</label>
                                                    <input type="text" class="form-control js-total-biaya" value="0" readonly>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end gap-2 mt-4">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-warning">Simpan & Selesaikan</button>
                                            </div>
                                        </form>
                                    </div></div></div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (!$reservasiList): ?>
                                <div class="text-center text-secondary py-5">Belum ada data pada status ini.</div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>No. Antrean</th>
                                        <?php if ($stage === 'menunggu_konfirmasi' || $stage === 'dikonfirmasi'): ?><th>Tanggal Servis</th><?php endif; ?>
                                        <th>Pelanggan</th>
                                        <th>Kendaraan</th>
                                        <?php if ($stage === 'selesai'): ?><th>Mekanik</th><?php endif; ?>
                                        <th>Jenis Servis</th>
                                        <?php if ($stage === 'menunggu_konfirmasi'): ?><th>Keluhan</th><?php endif; ?>
                                        <?php if ($stage === 'dikonfirmasi'): ?><th>Status Kehadiran</th><?php endif; ?>
                                        <?php if ($stage === 'selesai'): ?><th>Total Biaya</th><?php endif; ?>
                                        <th><?= htmlspecialchars(stageTimeLabel($stage)) ?></th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($reservasiList as $item): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($item['no_antrian']) ?></td>
                                        <?php if ($stage === 'menunggu_konfirmasi' || $stage === 'dikonfirmasi'): ?><td><?= htmlspecialchars(formatDateIndonesia($item['tanggal_servis'])) ?></td><?php endif; ?>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($item['pelanggan']) ?></div>
                                            <div class="text-secondary small"><?= htmlspecialchars(displayPhone($item['no_whatsapp'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($item['no_plat']) ?></div>
                                            <div class="text-secondary small"><?= htmlspecialchars($item['tipe_model'] ?: '-') ?></div>
                                        </td>
                                        <?php if ($stage === 'selesai'): ?><td><?= htmlspecialchars($item['mekanik_nama'] ?: '-') ?></td><?php endif; ?>
                                        <td><?= htmlspecialchars($item['layanan']) ?></td>
                                        <?php if ($stage === 'menunggu_konfirmasi'): ?><td><?= htmlspecialchars($item['keluhan'] ?: '-') ?></td><?php endif; ?>
                                        <?php if ($stage === 'dikonfirmasi'): ?><td><span class="badge <?= getKehadiranBadgeClass($item['kehadiran']) ?>"><?= htmlspecialchars($item['kehadiran']) ?></span></td><?php endif; ?>
                                        <?php if ($stage === 'selesai'): ?><td>Rp <?= number_format((float) $item['total_biaya'], 0, ',', '.') ?></td><?php endif; ?>
                                        <td><?= htmlspecialchars(stageTimeValue($item, $stage)) ?></td>
                                        <td class="text-end">
                                            <a href="index.php?page=status_servis&stage=<?= urlencode($stage) ?>&id=<?= (int) $item['id'] ?>&q=<?= urlencode($search) ?>" class="btn btn-sm btn-outline-primary">Detail</a>

                                            <?php if ($stage === 'menunggu_konfirmasi'): ?>
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal<?= (int) $item['id'] ?>">Konfirmasi</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelNewModal<?= (int) $item['id'] ?>">Batalkan</button>
                                            <?php endif; ?>

                                            <?php if ($stage === 'dikonfirmasi'): ?>
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#hadirModal<?= (int) $item['id'] ?>">Konfirmasi Kehadiran</button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#pendingModal<?= (int) $item['id'] ?>">Tidak Hadir</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#batalModal<?= (int) $item['id'] ?>">Batalkan</button>
                                            <?php endif; ?>

                                            <?php if ($stage === 'menunggu_antrean'): ?>
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#prosesModal<?= (int) $item['id'] ?>">Proses Servis</button>
                                            <?php endif; ?>

                                            <?php if ($stage === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#callbackModal<?= (int) $item['id'] ?>">Callback</button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#batalPendingModal<?= (int) $item['id'] ?>">Batalkan</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <?php if ($stage === 'menunggu_konfirmasi'): ?>
                                        <div class="modal fade" id="confirmModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4">
                                                <h5 class="mb-3">Konfirmasi Reservasi</h5>
                                                <p class="text-secondary mb-4">Apakah reservasi ini ingin dikonfirmasi?</p>
                                                <form method="post" action="index.php?page=status_servis&stage=menunggu_konfirmasi">
                                                    <input type="hidden" name="action" value="confirm_reservasi">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <input type="hidden" name="redirect_to" value="index.php?page=status_servis&stage=menunggu_konfirmasi&mode=<?= urlencode($filterMode) ?>&q=<?= urlencode($search) ?>">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Ya, Konfirmasi</button>
                                                    </div>
                                                </form>
                                            </div></div></div>
                                        </div>
                                        <div class="modal fade" id="cancelNewModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4">
                                                <h5 class="mb-3">Batalkan Reservasi</h5>
                                                <p class="text-secondary mb-4">Apakah Anda yakin ingin membatalkan reservasi ini?</p>
                                                <form method="post" action="index.php?page=status_servis&stage=menunggu_konfirmasi">
                                                    <input type="hidden" name="action" value="cancel_reservasi">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <input type="hidden" name="redirect_to" value="index.php?page=status_servis&stage=menunggu_konfirmasi&mode=<?= urlencode($filterMode) ?>&q=<?= urlencode($search) ?>">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                                                    </div>
                                                </form>
                                            </div></div></div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($stage === 'dikonfirmasi'): ?>
                                        <div class="modal fade" id="hadirModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4">
                                                <h5 class="mb-3">Konfirmasi Kehadiran</h5>
                                                <p class="text-secondary mb-4">Apakah pelanggan sudah hadir di bengkel?</p>
                                                <form method="post" action="index.php?page=status_servis&stage=dikonfirmasi">
                                                    <input type="hidden" name="action" value="confirm_kehadiran">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <input type="hidden" name="redirect_to" value="index.php?page=status_servis&stage=dikonfirmasi&q=<?= urlencode($search) ?>">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Ya, Hadir</button>
                                                    </div>
                                                </form>
                                            </div></div></div>
                                        </div>
                                        <div class="modal fade" id="pendingModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4">
                                                <h5 class="mb-3">Pindah ke Pending</h5>
                                                <p class="text-secondary mb-4">Pindahkan pelanggan ke status pending kehadiran?</p>
                                                <form method="post" action="index.php?page=status_servis&stage=dikonfirmasi">
                                                    <input type="hidden" name="action" value="mark_pending">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <input type="hidden" name="redirect_to" value="index.php?page=status_servis&stage=dikonfirmasi&q=<?= urlencode($search) ?>">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-warning">Ya, Pending</button>
                                                    </div>
                                                </form>
                                            </div></div></div>
                                        </div>
                                        <div class="modal fade" id="batalModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4">
                                                <h5 class="mb-3">Batalkan Reservasi</h5>
                                                <p class="text-secondary mb-4">Apakah Anda yakin ingin membatalkan reservasi ini?</p>
                                                <form method="post" action="index.php?page=status_servis&stage=dikonfirmasi">
                                                    <input type="hidden" name="action" value="cancel_reservasi">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <input type="hidden" name="redirect_to" value="index.php?page=status_servis&stage=dikonfirmasi&q=<?= urlencode($search) ?>">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                                                    </div>
                                                </form>
                                            </div></div></div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($stage === 'menunggu_antrean'): ?>
                                        <div class="modal fade" id="prosesModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog"><div class="modal-content"><div class="modal-body p-4">
                                                <h5 class="mb-3">Mulai Proses Servis</h5>
                                                <div class="helper-box mb-3">
                                                    <div class="detail-row"><span>No. Antrean</span><strong><?= htmlspecialchars($item['no_antrian']) ?></strong></div>
                                                    <div class="detail-row"><span>Pelanggan</span><strong><?= htmlspecialchars($item['pelanggan']) ?></strong></div>
                                                    <div class="detail-row"><span>Kendaraan</span><strong><?= htmlspecialchars(($item['tipe_model'] ?: '-') . ' / ' . $item['no_plat']) ?></strong></div>
                                                    <div class="detail-row"><span>Layanan</span><strong><?= htmlspecialchars($item['layanan']) ?></strong></div>
                                                </div>
                                                <form method="post" action="index.php?page=status_servis&stage=menunggu_antrean">
                                                    <input type="hidden" name="action" value="start_service">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">Pilih Mekanik</label>
                                                        <select name="mekanik_id" class="form-select" required>
                                                            <option value="">Pilih mekanik tersedia</option>
                                                            <?php foreach ($mekanikList as $mekanik): ?>
                                                                <option value="<?= (int) $mekanik['id'] ?>"><?= htmlspecialchars($mekanik['nama']) ?> - <?= htmlspecialchars($mekanik['status']) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Mulai Proses Servis</button>
                                                    </div>
                                                </form>
                                            </div></div></div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($stage === 'pending'): ?>
                                        <div class="modal fade" id="callbackModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4">
                                                <h5 class="mb-3">Callback Antrean</h5>
                                                <p class="text-secondary mb-4">Panggil kembali pelanggan ini ke antrean servis?</p>
                                                <form method="post" action="index.php?page=status_servis&stage=pending">
                                                    <input type="hidden" name="action" value="callback_pending">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <input type="hidden" name="redirect_to" value="index.php?page=status_servis&stage=pending&q=<?= urlencode($search) ?>">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Ya, Callback</button>
                                                    </div>
                                                </form>
                                            </div></div></div>
                                        </div>
                                        <div class="modal fade" id="batalPendingModal<?= (int) $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-body p-4">
                                                <h5 class="mb-3">Batalkan Reservasi</h5>
                                                <p class="text-secondary mb-4">Apakah Anda yakin ingin membatalkan reservasi ini?</p>
                                                <form method="post" action="index.php?page=status_servis&stage=pending">
                                                    <input type="hidden" name="action" value="cancel_reservasi">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <input type="hidden" name="redirect_to" value="index.php?page=status_servis&stage=pending&q=<?= urlencode($search) ?>">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                                                    </div>
                                                </form>
                                            </div></div></div>
                                        </div>
                                    <?php endif; ?>

                                <?php endforeach; ?>
                                <?php if (!$reservasiList): ?>
                                    <?php
                                    $colspan = 6;
                                    if ($stage === 'menunggu_konfirmasi') {
                                        $colspan = 8;
                                    } elseif ($stage === 'dikonfirmasi') {
                                        $colspan = 8;
                                    } elseif ($stage === 'menunggu_antrean') {
                                        $colspan = 6;
                                    } elseif ($stage === 'pending') {
                                        $colspan = 6;
                                    } elseif ($stage === 'selesai') {
                                        $colspan = 7;
                                    }
                                    ?>
                                    <tr><td colspan="<?= $colspan ?>" class="text-center text-secondary py-5">Belum ada data pada status ini.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <?= renderPagination($currentPage, $totalPages, [
                        'page' => 'status_servis',
                        'stage' => $stage,
                        'q' => $search,
                        'mode' => $stage === 'menunggu_konfirmasi' ? $filterMode : null,
                    ]) ?>
                </div>
            </div>

            <div class="card detail-panel">
                <div class="card-body">
                    <div class="section-title">Detail Reservasi</div>
                    <?php if ($selected): ?>
                        <div class="detail-list">
                            <div class="detail-row"><span>Nomor Antrean</span><strong><?= htmlspecialchars($selected['no_antrian']) ?></strong></div>
                            <div class="detail-row"><span>Pelanggan</span><strong><?= htmlspecialchars($selected['pelanggan']) ?></strong></div>
                            <div class="detail-row"><span>Nomor HP</span><strong><?= htmlspecialchars(displayPhone($selected['no_whatsapp'])) ?></strong></div>
                            <div class="detail-row"><span>Kendaraan</span><strong><?= htmlspecialchars(($selected['tipe_model'] ?: '-') . ' / ' . $selected['no_plat']) ?></strong></div>
                            <div class="detail-row"><span>Layanan</span><strong><?= htmlspecialchars($selected['layanan']) ?></strong></div>
                            <div class="detail-row"><span>Kehadiran</span><strong><?= htmlspecialchars($selected['kehadiran']) ?></strong></div>
                            <?php if ($selected['mekanik_nama']): ?><div class="detail-row"><span>Mekanik</span><strong><?= htmlspecialchars($selected['mekanik_nama']) ?></strong></div><?php endif; ?>
                            <?php if ($stage === 'diproses' || $stage === 'selesai'): ?><div class="detail-row"><span>Waktu Proses</span><strong><?= htmlspecialchars(formatDateTimeIndonesia($selected['waktu_mulai_servis'])) ?></strong></div><?php endif; ?>
                            <?php if ($stage === 'pending'): ?><div class="detail-row"><span>Waktu Pending</span><strong><?= htmlspecialchars(formatDateTimeIndonesia($selected['waktu_pending'])) ?></strong></div><?php endif; ?>
                            <?php if ($stage === 'selesai'): ?>
                                <div class="detail-row"><span>Hasil Servis</span><strong><?= htmlspecialchars($selected['hasil_servis'] ?: '-') ?></strong></div>
                                <div class="detail-row"><span>Biaya Jasa</span><strong>Rp <?= number_format((float) $selected['biaya_jasa'], 0, ',', '.') ?></strong></div>
                                <div class="detail-row"><span>Total Biaya</span><strong>Rp <?= number_format((float) $selected['total_biaya'], 0, ',', '.') ?></strong></div>
                                <div class="helper-box mt-3">
                                    <div class="fw-semibold mb-2">Sparepart</div>
                                    <?php if ($sparepartList): ?>
                                        <?php foreach ($sparepartList as $sparepart): ?>
                                            <div class="detail-row">
                                                <span><?= htmlspecialchars($sparepart['nama_item']) ?> x<?= (int) $sparepart['qty'] ?></span>
                                                <strong>Rp <?= number_format((float) $sparepart['subtotal'], 0, ',', '.') ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-secondary">Tidak ada item sparepart.</div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-secondary">Pilih data pada tabel untuk melihat detail.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('js-add-sparepart')) {
        var table = event.target.closest('form').querySelector('[data-sparepart-table] tbody');
        var row = document.createElement('tr');
        row.innerHTML = '<td><input type="text" name="sparepart_nama[]" class="form-control"></td><td><input type="number" name="sparepart_qty[]" class="form-control js-sparepart-qty" value="1" min="1"></td><td><input type="number" name="sparepart_harga[]" class="form-control js-sparepart-harga" value="0" min="0"></td><td><input type="text" class="form-control js-sparepart-subtotal" value="0" readonly></td><td><button type="button" class="btn btn-outline-danger btn-sm js-remove-item">Hapus</button></td>';
        table.appendChild(row);
    }

    if (event.target.classList.contains('js-remove-item')) {
        var rows = event.target.closest('tbody').querySelectorAll('tr');
        if (rows.length > 1) {
            event.target.closest('tr').remove();
            document.dispatchEvent(new Event('input'));
        }
    }
});

document.addEventListener('input', function () {
    document.querySelectorAll('.modal').forEach(function (modal) {
        var biayaJasaInput = modal.querySelector('.js-biaya-jasa');
        if (!biayaJasaInput) {
            return;
        }

        var total = parseFloat(biayaJasaInput.value || '0');
        modal.querySelectorAll('tbody tr').forEach(function (row) {
            var qty = parseFloat((row.querySelector('.js-sparepart-qty') || {}).value || '0');
            var harga = parseFloat((row.querySelector('.js-sparepart-harga') || {}).value || '0');
            var subtotal = qty * harga;
            var subtotalField = row.querySelector('.js-sparepart-subtotal');
            if (subtotalField) {
                subtotalField.value = subtotal.toLocaleString('id-ID');
            }
            total += subtotal;
        });

        var totalField = modal.querySelector('.js-total-biaya');
        if (totalField) {
            totalField.value = total.toLocaleString('id-ID');
        }
    });
});

document.dispatchEvent(new Event('input'));
</script>
