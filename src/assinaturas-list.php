<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<head>
    <?php $subTitle = 'Assinaturas'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>
<body>
<div class="wrapper">
    <?php $subTitle = 'Assinaturas'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>
    <div class="page-content">
        <div class="container-xxl">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center gap-2">
                            <h4 class="card-title m-0">Assinaturas</h4>
                        </div>
                        <?php $list = array_values(array_filter(termosList(), function($t){ return ($t['status'] ?? '') === 'assinado'; })); ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Equipamento</th>
                                        <th>Colaborador</th>
                                        <th>Assinado em</th>
                                        <th>Assinatura (Colaborador)</th>
                                        <th>Assinatura (Responsável)</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($list as $t): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($t['equip_nome'] ?? '') ?></td>
                                        <td><?php echo htmlspecialchars($t['colab_nome'] ?? '') ?></td>
                                        <td><?php echo htmlspecialchars($t['assinado_em'] ?? '') ?></td>
                                        <td><?php if (!empty($t['assinaturas']['colaborador'])): ?><img src="<?php echo htmlspecialchars($t['assinaturas']['colaborador']) ?>" class="img-thumbnail" style="max-width:120px"><?php endif; ?></td>
                                        <td><?php if (!empty($t['assinaturas']['responsavel'])): ?><img src="<?php echo htmlspecialchars($t['assinaturas']['responsavel']) ?>" class="img-thumbnail" style="max-width:120px"><?php endif; ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="termo-assinar.php?id=<?php echo (int)$t['id'] ?>" class="btn btn-light btn-sm">Ver Termo</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($list)): ?>
                                    <tr><td colspan="6" class="text-center text-muted">Nenhuma assinatura registrada</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'partials/footer.php' ?>
        </div>
    </div>
</div>
<?php include 'partials/vendor-scripts.php' ?>
</body>
</html>
