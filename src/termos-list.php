<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<head>
    <?php $subTitle = 'Termos e Assinaturas'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>
<body>
<div class="wrapper">
    <?php $subTitle = 'Termos e Assinaturas'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>
    <div class="page-content">
        <div class="container-xxl">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center gap-2">
                            <h4 class="card-title m-0">Termos</h4>
                            <div class="d-flex gap-2">
                                <a href="termo-criar.php" class="btn btn-sm btn-primary">Novo Termo</a>
                            </div>
                        </div>
                        <?php $termos = termosList(); $equip = equipamentosList(); $filter = $_GET['filter'] ?? ''; if ($filter==='pendente') { $termos = array_values(array_filter($termos, function($t){ return ($t['status'] ?? '') === 'pendente'; })); } $fmt=function($s){ if(!$s) return ''; try{ $dt=new DateTime((string)$s, new DateTimeZone('UTC')); $dt->setTimezone(new DateTimeZone('America/Sao_Paulo')); return $dt->format('d/m/Y \\à\\s H:i \\h\\o\\r\\a\\s'); }catch(Exception $e){ $ts=strtotime((string)$s); return $ts?date('d/m/Y \\à\\s H:i \\h\\o\\r\\a\\s',$ts):($s??''); } }; ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Equipamento</th>
                                        <th>Colaborador</th>
                                        <th>Status</th>
                                        <th>Criado</th>
                                        <th>Assinado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($termos as $t): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($t['tipo']) ?></td>
                                        <td><?php echo htmlspecialchars($t['equip_nome'] ?? '') ?></td>
                                        <td><?php echo htmlspecialchars($t['colab_nome'] ?? '') ?></td>
                                        <td>
                                            <?php $st = $t['status'] ?? (((int)($t['assinado'] ?? 0)) ? 'assinado' : 'pendente'); $badge='badge-soft-secondary'; if($st==='pendente') $badge='badge-soft-warning'; if($st==='assinado') $badge='badge-soft-success'; ?>
                                            <span class="badge <?php echo $badge ?>"><?php echo htmlspecialchars($st) ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($fmt($t['criado_em'] ?? '')) ?></td>
                                        <td><?php echo htmlspecialchars($fmt($t['assinado_em'] ?? '')) ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <?php $isPend = (($t['status'] ?? '') === 'pendente'); ?>
                                                <a href="termo-assinar.php?id=<?php echo (int)$t['id'] ?>" class="btn btn-soft-primary btn-sm"><?php echo $isPend ? 'Assinar' : 'Ver Termo' ?></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($termos)): ?>
                                    <tr><td colspan="7" class="text-center text-muted">Nenhum termo criado</td></tr>
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
