<?php
$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status_notif'] ?? '');
$currentPage = max(1, (int) ($_GET['p'] ?? 1));
$perPage = 5;

$baseSql = "
    FROM notifikasi_wa nw
    INNER JOIN reservasi r ON r.id = nw.reservasi_id
    INNER JOIN users u ON u.id = r.user_id
    WHERE 1 = 1
";

$params = [];

if ($search !== '') {
    $baseSql .= " AND (u.nama LIKE :search OR r.no_antrian LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($statusFilter !== '') {
    $baseSql .= " AND nw.status = :status";
    $params[':status'] = $statusFilter;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) AS total " . $baseSql);
$countStmt->execute($params);
$totalRows = (int) ($countStmt->fetch()['total'] ?? 0);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;

$sql = "
    SELECT
        nw.sent_at,
        nw.jenis,
        nw.status,
        r.no_antrian,
        u.nama AS pelanggan
    " . $baseSql . "
    ORDER BY nw.sent_at DESC
    LIMIT " . (int) $perPage . " OFFSET " . (int) $offset . "
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notifikasiList = $stmt->fetchAll();
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="placeholder-note mb-4">
            Integrasi WhatsApp saat ini masih menggunakan mode placeholder. Setiap proses kirim hanya dicatat sebagai log internal pada tabel `notifikasi_wa`.
        </div>
        <form method="get" action="index.php" class="toolbar">
            <input type="hidden" name="page" value="notifikasi">
            <div class="toolbar-group">
                <select name="status_notif" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Terkirim" <?= $statusFilter === 'Terkirim' ? 'selected' : '' ?>>Terkirim</option>
                    <option value="Gagal" <?= $statusFilter === 'Gagal' ? 'selected' : '' ?>>Gagal</option>
                </select>
            </div>
            <div class="toolbar-group">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Cari pelanggan atau nomor antrian">
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No Antrian</th>
                        <th>Pelanggan</th>
                        <th>Jenis Notifikasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notifikasiList as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars(formatDateTimeIndonesia($item['sent_at'])) ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($item['no_antrian']) ?></td>
                            <td><?= htmlspecialchars($item['pelanggan']) ?></td>
                            <td><?= htmlspecialchars($item['jenis']) ?></td>
                            <td>
                                <span class="badge <?= $item['status'] === 'Terkirim' ? 'text-bg-success' : 'text-bg-danger' ?>">
                                    <?= htmlspecialchars($item['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$notifikasiList): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">Data notifikasi belum tersedia.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= renderPagination($currentPage, $totalPages, [
            'page' => 'notifikasi',
            'q' => $search,
            'status_notif' => $statusFilter
        ]) ?>
    </div>
</div>
