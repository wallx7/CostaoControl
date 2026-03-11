<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php require_once 'services/csrf.php'; ?>
<?php
$erro = null; $viewCat = null; $editCat = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf'] ?? '';
    if (!csrfValidate($token)) { $erro = 'Token inválido'; }
    else {
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            categoriaDelete($id);
            header('Location: inventario-categorias.php');
            exit;
        } elseif ($action === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $icon = trim($_POST['icon'] ?? 'solar:monitor-bold-duotone');
            if ($id && $nome !== '') { categoriaUpdate($id, $nome, $icon); }
            header('Location: inventario-categorias.php');
            exit;
        } elseif ($action === 'add') {
            $nome = trim($_POST['nome'] ?? '');
            $icon = trim($_POST['icon'] ?? 'solar:monitor-bold-duotone');
            if ($nome !== '') { categoriaAdd($nome, $icon); }
            header('Location: inventario-categorias.php');
            exit;
        }
    }
}
if (isset($_GET['view'])) { $viewCat = categoriaGet((int)$_GET['view']); }
if (isset($_GET['edit'])) { $editCat = categoriaGet((int)$_GET['edit']); }
$categorias = categoriasList();
$equip = equipamentosList();
$countPorCat = [];
foreach ($categorias as $c) { $countPorCat[$c['id']] = 0; }
foreach ($equip as $e) {
    foreach ($categorias as $c) {
        if (($e['tipo']??'') === $c['nome']) { $countPorCat[$c['id']]++; }
    }
}
?>
<head>
    <?php $subTitle = 'Categorias'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>
<body>
<div class="wrapper">
    <?php $subTitle = 'Categorias'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>
    <div class="page-content">
        <div class="container-xxl">
            <div class="row">
                <?php foreach (array_slice($categorias,0,4) as $idx=>$cat): ?>
                <div class="col-md-6 col-xl-3">
                    <div class="card"><div class="card-body text-center">
                        <div class="rounded bg-secondary-subtle d-flex align-items-center justify-content-center mx-auto">
                            <iconify-icon icon="<?php echo htmlspecialchars($cat['icon']) ?>" class="fs-48"></iconify-icon>
                        </div>
                        <h4 class="mt-3 mb-0"><?php echo htmlspecialchars($cat['nome']) ?></h4>
                    </div></div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center gap-1">
                            <h4 class="card-title flex-grow-1">Lista de Categorias</h4>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Adicionar Categoria</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th style="width: 20px;"></th>
                                        <th>Categoria</th>
                                        <th>Icone</th>
                                        <th>ID</th>
                                        <th>Qtd Equipamentos</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categorias as $cat): ?>
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input"></td>
                                        <td><?php echo htmlspecialchars($cat['nome']) ?></td>
                                        <td><iconify-icon icon="<?php echo htmlspecialchars($cat['icon']) ?>" class="fs-20"></iconify-icon></td>
                                        <td>CAT-<?php echo (int)$cat['id'] ?></td>
                                        <td><?php echo $countPorCat[$cat['id']] ?? 0 ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="inventario-categorias.php?view=<?php echo (int)$cat['id'] ?>" class="btn btn-light btn-sm"><iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon></a>
                                                <a href="inventario-categorias.php?edit=<?php echo (int)$cat['id'] ?>" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon></a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo (int)$cat['id'] ?>">
                                                    <button class="btn btn-soft-danger btn-sm" type="submit"><iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Categoria</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <?php if ($viewCat): ?>
                                <p><strong>Nome:</strong> <?php echo htmlspecialchars($viewCat['nome']) ?></p>
                                <p><strong>Icone:</strong> <iconify-icon icon="<?php echo htmlspecialchars($viewCat['icon']) ?>" class="fs-20"></iconify-icon></p>
                                <p><strong>ID:</strong> CAT-<?php echo (int)$viewCat['id'] ?></p>
                                <p><strong>Equipamentos:</strong> <?php echo $countPorCat[$viewCat['id']] ?? 0 ?></p>
                            <?php else: ?>
                                <p class="text-muted">Selecione uma categoria.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar Categoria</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="post">
                            <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="id" value="<?php echo (int)($editCat['id'] ?? 0) ?>">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nome</label>
                                    <input class="form-control" name="nome" value="<?php echo htmlspecialchars($editCat['nome'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Icone (iconify)</label>
                                    <input class="form-control" name="icon" value="<?php echo htmlspecialchars($editCat['icon'] ?? '') ?>" placeholder="solar:monitor-bold-duotone" required>
                                </div>
                            </div>
                            <div class="modal-footer"><button class="btn btn-primary" type="submit">Salvar</button></div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Adicionar Categoria</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="post">
                            <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                            <input type="hidden" name="action" value="add">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nome</label>
                                    <input class="form-control" name="nome" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Icone (iconify)</label>
                                    <input class="form-control" name="icon" placeholder="solar:monitor-bold-duotone" required>
                                </div>
                            </div>
                            <div class="modal-footer"><button class="btn btn-primary" type="submit">Adicionar</button></div>
                        </form>
                    </div>
                </div>
            </div>

            <?php include 'partials/footer.php' ?>
        </div>
    </div>
</div>
<?php include 'partials/vendor-scripts.php' ?>
<script>
<?php if ($viewCat): ?>
new bootstrap.Modal(document.getElementById('viewModal')).show();
<?php endif; ?>
<?php if ($editCat): ?>
new bootstrap.Modal(document.getElementById('editModal')).show();
<?php endif; ?>
</script>
</body>
</html>
