<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>

<head>
    <?php $subTitle = 'Usuários (TI)'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>

<body>
<div class="wrapper">
    <?php $subTitle = 'Usuários (TI)'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>

    <div class="page-content">
        <div class="container-xxl">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title">Usuários que usam o site (TI)</h4>
                        </div>
                        <div class="card-body">
                            <?php $u = $_SESSION['user'] ?? null; ?>
                            <?php if (!$u): ?>
                                <div class="alert alert-warning">Nenhum usuário logado.</div>
                            <?php else: ?>
                                <?php
                                $equip = equipamentosList();
                                usort($equip, function($a,$b){ return strcmp(($b['criado_em']??''), ($a['criado_em']??'')); });
                                $lastEquip = $equip[0] ?? null;
                                $termos = termosList();
                                $ass = array_values(array_filter($termos, function($t){ return ($t['status']??'')==='assinado'; }));
                                usort($ass, function($a,$b){ return strcmp(($b['assinado_em']??''), ($a['assinado_em']??'')); });
                                $lastEntrega = $ass[0] ?? null;
                                ?>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 table-hover table-centered">
                                        <thead class="bg-light-subtle">
                                            <tr>
                                                <th>Foto</th>
                                                <th>Nome</th>
                                                <th>Último item cadastrado</th>
                                                <th>Última entrega</th>
                                                <th>Para o usuário</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                                        <span class="text-primary fw-semibold">
                                                            <?php echo strtoupper(substr($u['nome'] ?? 'TI',0,1)) ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($u['nome'] ?? '') ?></td>
                                                <td>
                                                    <?php if ($lastEquip): ?>
                                                        <div><?php echo htmlspecialchars($lastEquip['nome'] ?? '') ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($lastEquip['criado_em'] ?? '') ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($lastEntrega): ?>
                                                        <?php echo htmlspecialchars(($lastEntrega['codigo'] ?? '').' • '.($lastEntrega['assinado_em'] ?? '')) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($lastEntrega['colab_nome'] ?? '') ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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
