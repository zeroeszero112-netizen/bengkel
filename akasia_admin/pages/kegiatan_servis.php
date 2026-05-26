<?php
$kegiatanStmt = $pdo->query("
    SELECT ks.id, ks.nama_kegiatan, ks.jenis_layanan_id, jl.nama AS layanan
    FROM kegiatan_servis ks
    INNER JOIN jenis_layanan jl ON jl.id = ks.jenis_layanan_id
    ORDER BY jl.id ASC, ks.id ASC
");
$kegiatanServisList = $kegiatanStmt->fetchAll();
$jenisLayananStmt = $pdo->query("SELECT id, nama FROM jenis_layanan ORDER BY id ASC");
$jenisLayananList = $jenisLayananStmt->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$showForm = ($_GET['form'] ?? '') === 'tambah' || $editId > 0;
$kegiatanEdit = [
    'id' => 0,
    'jenis_layanan_id' => 0,
    'nama_kegiatan' => ''
];

if ($editId > 0) {
    $editStmt = $pdo->prepare("SELECT id, jenis_layanan_id, nama_kegiatan FROM kegiatan_servis WHERE id = :id LIMIT 1");
    $editStmt->execute([':id' => $editId]);
    $kegiatanEditData = $editStmt->fetch();
    if ($kegiatanEditData) {
        $kegiatanEdit = $kegiatanEditData;
    }
}
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="toolbar">
            <div class="toolbar-group">
                <a href="index.php?page=kegiatan_servis&form=tambah" class="btn btn-primary">Tambah Kegiatan</a>
            </div>
        </div>

        <?php if ($showForm): ?>
            <div class="card border form-panel mb-4">
                <div class="card-body">
                    <div class="section-title mb-3"><?= $kegiatanEdit['id'] ? 'Ubah Kegiatan Servis' : 'Tambah Kegiatan Servis' ?></div>
                    <form method="post" action="index.php?page=kegiatan_servis">
                        <input type="hidden" name="action" value="save_kegiatan_servis">
                        <input type="hidden" name="id" value="<?= (int) $kegiatanEdit['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Jenis Layanan</label>
                                <select name="jenis_layanan_id" class="form-select" required>
                                    <option value="">Pilih Jenis Layanan</option>
                                    <?php foreach ($jenisLayananList as $layanan): ?>
                                        <option value="<?= (int) $layanan['id'] ?>" <?= (int) $kegiatanEdit['jenis_layanan_id'] === (int) $layanan['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($layanan['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Nama Kegiatan</label>
                                <input type="text" name="nama_kegiatan" class="form-control" value="<?= htmlspecialchars($kegiatanEdit['nama_kegiatan']) ?>" required>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="index.php?page=kegiatan_servis" class="btn btn-outline-secondary">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Jenis Layanan</th>
                        <th>Kegiatan Servis</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kegiatanServisList as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($item['layanan']) ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($item['nama_kegiatan']) ?></td>
                            <td class="text-end">
                                <a href="index.php?page=kegiatan_servis&edit=<?= (int) $item['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" action="index.php?page=kegiatan_servis" class="d-inline">
                                    <input type="hidden" name="action" value="delete_kegiatan_servis">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kegiatan servis ini?');">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$kegiatanServisList): ?>
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-5">Data kegiatan servis belum tersedia.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
