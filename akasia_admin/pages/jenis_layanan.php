<?php
$jenisStmt = $pdo->query("SELECT id, nama, deskripsi, harga FROM jenis_layanan ORDER BY id ASC");
$jenisLayananList = $jenisStmt->fetchAll();
$editId = (int) ($_GET['edit'] ?? 0);
$showForm = ($_GET['form'] ?? '') === 'tambah' || $editId > 0;
$jenisEdit = [
    'id' => 0,
    'nama' => '',
    'deskripsi' => '',
    'harga' => 0
];

if ($editId > 0) {
    $editStmt = $pdo->prepare("SELECT id, nama, deskripsi, harga FROM jenis_layanan WHERE id = :id LIMIT 1");
    $editStmt->execute([':id' => $editId]);
    $jenisEditData = $editStmt->fetch();
    if ($jenisEditData) {
        $jenisEdit = $jenisEditData;
    }
}
?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="toolbar">
            <div class="toolbar-group">
                <a href="index.php?page=jenis_layanan&form=tambah" class="btn btn-primary">Tambah Layanan</a>
            </div>
        </div>

        <?php if ($showForm): ?>
            <div class="card border form-panel mb-4">
                <div class="card-body">
                    <div class="section-title mb-3"><?= $jenisEdit['id'] ? 'Ubah Jenis Layanan' : 'Tambah Jenis Layanan' ?></div>
                    <form method="post" action="index.php?page=jenis_layanan">
                        <input type="hidden" name="action" value="save_jenis_layanan">
                        <input type="hidden" name="id" value="<?= (int) $jenisEdit['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nama Layanan</label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($jenisEdit['nama']) ?>" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($jenisEdit['deskripsi'] ?: '') ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Harga Dasar</label>
                                <input type="number" name="harga" class="form-control" min="0" value="<?= (int) $jenisEdit['harga'] ?>">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="index.php?page=jenis_layanan" class="btn btn-outline-secondary">Batal</a>
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
                        <th>Deskripsi</th>
                        <th>Harga Dasar</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jenisLayananList as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($item['nama']) ?></td>
                            <td><?= htmlspecialchars($item['deskripsi']) ?></td>
                            <td>Rp <?= number_format((int) $item['harga'], 0, ',', '.') ?></td>
                            <td class="text-end">
                                <a href="index.php?page=jenis_layanan&edit=<?= (int) $item['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" action="index.php?page=jenis_layanan" class="d-inline">
                                    <input type="hidden" name="action" value="delete_jenis_layanan">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus jenis layanan ini?');">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$jenisLayananList): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">Data jenis layanan belum tersedia.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
