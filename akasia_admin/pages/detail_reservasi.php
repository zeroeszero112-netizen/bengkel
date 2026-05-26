<?php
$id = (int) ($_GET['id'] ?? 0);
$detail = findReservasi($pdo, $id);

if (!$detail) {
    echo '<div class="alert alert-warning shadow-sm">Data reservasi tidak ditemukan.</div>';
    return;
}

$sparepartStmt = $pdo->prepare("SELECT nama_item, qty, harga, subtotal FROM reservasi_sparepart WHERE reservasi_id = :reservasi_id ORDER BY id ASC");
$sparepartStmt->execute([':reservasi_id' => $id]);
$spareparts = $sparepartStmt->fetchAll();
?>

<div class="row g-4">
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title">Informasi Reservasi</div>
                <div class="detail-list">
                    <div class="detail-row"><span>Nomor Antrean</span><strong><?= htmlspecialchars($detail['no_antrian']) ?></strong></div>
                    <div class="detail-row"><span>Status</span><strong><span class="badge <?= getStatusBadgeClass($detail['status']) ?>"><?= htmlspecialchars(getStageMeta($detail['status'])['label']) ?></span></strong></div>
                    <div class="detail-row"><span>Jenis Reservasi</span><strong><?= htmlspecialchars($detail['jenis_reservasi']) ?></strong></div>
                    <div class="detail-row"><span>Tanggal Servis</span><strong><?= htmlspecialchars(formatDateIndonesia($detail['tanggal_servis'])) ?></strong></div>
                    <div class="detail-row"><span>Kehadiran</span><strong><?= htmlspecialchars($detail['kehadiran']) ?></strong></div>
                    <div class="detail-row"><span>Mekanik</span><strong><?= htmlspecialchars($detail['mekanik_nama'] ?: '-') ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title">Data Pelanggan</div>
                <div class="detail-list">
                    <div class="detail-row"><span>Nama</span><strong><?= htmlspecialchars($detail['pelanggan']) ?></strong></div>
                    <div class="detail-row"><span>No. WhatsApp</span><strong><?= htmlspecialchars(displayPhone($detail['no_whatsapp'])) ?></strong></div>
                    <div class="detail-row"><span>Alamat</span><strong><?= htmlspecialchars($detail['alamat'] ?: '-') ?></strong></div>
                    <div class="detail-row"><span>Kendaraan</span><strong><?= htmlspecialchars(($detail['tipe_model'] ?: '-') . ' / ' . $detail['no_plat']) ?></strong></div>
                    <div class="detail-row"><span>Jenis Kendaraan</span><strong><?= htmlspecialchars($detail['jenis_kendaraan'] ?: '-') ?></strong></div>
                    <div class="detail-row"><span>Tahun</span><strong><?= htmlspecialchars((string) ($detail['tahun'] ?: '-')) ?></strong></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="section-title">Informasi Servis</div>
                <div class="detail-list">
                    <div class="detail-row"><span>Jenis Servis</span><strong><?= htmlspecialchars($detail['layanan']) ?></strong></div>
                    <div class="detail-row"><span>Keluhan</span><strong><?= htmlspecialchars($detail['keluhan'] ?: '-') ?></strong></div>
                    <div class="detail-row"><span>Hasil Servis</span><strong><?= htmlspecialchars($detail['hasil_servis'] ?: '-') ?></strong></div>
                    <div class="detail-row"><span>Biaya Jasa</span><strong>Rp <?= number_format((float) $detail['biaya_jasa'], 0, ',', '.') ?></strong></div>
                    <div class="detail-row"><span>Total Biaya</span><strong>Rp <?= number_format((float) $detail['total_biaya'], 0, ',', '.') ?></strong></div>
                    <div class="detail-row"><span>Catatan Tambahan</span><strong><?= htmlspecialchars($detail['catatan_tambahan'] ?: '-') ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <div class="section-title">Sparepart Manual</div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nama Item</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($spareparts as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nama_item']) ?></td>
                            <td><?= (int) $item['qty'] ?></td>
                            <td>Rp <?= number_format((float) $item['harga'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format((float) $item['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$spareparts): ?>
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">Belum ada item sparepart.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
