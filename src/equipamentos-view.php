<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php require_once 'services/csrf.php'; ?>

<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$equip = null;
foreach (equipamentosList() as $e) { if ((int)$e['id'] === $id) { $equip = $e; break; } }
$cl = $equip ? checklistGet($id) : checklistDefaults();
$title = $equip ? ('Detalhes do Equipamento') : 'Equipamento não encontrado';

if ($equip && $_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='upload_anexo') {
    $token = $_POST['csrf_token'] ?? '';
    if (!csrfValidate($token)) {
        $msg = 'Token inválido.';
    } else {
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error']===UPLOAD_ERR_OK) {
            $orig = $_FILES['arquivo']['name'];
            $size = (int)$_FILES['arquivo']['size'];
            $tmp  = $_FILES['arquivo']['tmp_name'];
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allow = ['pdf','jpg','jpeg','png','doc','docx','xls','xlsx','txt'];
            if (!in_array($ext, $allow)) {
                $msg = 'Tipo de arquivo não permitido.';
            } else {
                $dir = __DIR__ . '/assets/uploads/equipamentos/' . $id;
                if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
                $fname = uniqid('anx_', true) . '.' . $ext;
                $dest = $dir . '/' . $fname;
                if (move_uploaded_file($tmp, $dest)) {
                    $tipo = $_POST['tipo'] ?? '';
                    $rel = 'assets/uploads/equipamentos/' . $id . '/' . $fname;
                    anexoAdd($id, $tipo, $orig, $rel, $size);
                    $uid = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
                    logAdd($uid, 'upload_anexo', 'equipamentos', $id, ['file'=>$orig,'dest'=>$rel]);
                    $msg = 'Anexo enviado com sucesso.';
                } else {
                    $msg = 'Falha ao salvar arquivo.';
                }
            }
        } else {
            $msg = 'Nenhum arquivo enviado.';
        }
    }
}
 
?>

<head>
    <?php $subTitle = $title; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>

<body>
<div class="wrapper">
    <?php $subTitle = $title; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>

    <div class="page-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <?php if (!$equip): ?>
                                <div class="alert alert-danger">Equipamento não encontrado.</div>
                            <?php else: ?>
                                <?php
                                $anexosTop = anexosList($id);
                                $imgAnexos = array_values(array_filter($anexosTop, function($ax){ return preg_match('/\.(jpg|jpeg|png|gif)$/i', $ax['caminho'] ?? ''); }));
                                $sigPos = null; $signature = null;
                                for ($i=0; $i<count($imgAnexos); $i++) {
                                    $nm = strtolower($imgAnexos[$i]['nome_original'] ?? '');
                                    $tp = strtolower($imgAnexos[$i]['tipo'] ?? '');
                                    if (str_contains($nm,'assinatura') || $tp==='assinatura' || ($tp==='termo_responsabilidade' && preg_match('/\.(jpg|jpeg|png|gif)$/i', $imgAnexos[$i]['caminho'] ?? ''))) { $sigPos = $i; $signature = $imgAnexos[$i]; break; }
                                }
                                if ($sigPos!==null) { array_splice($imgAnexos, $sigPos, 1); }
                                $imgs = $imgAnexos;
                                if (!$imgs) {
                                    $samples = ['p-1.png','p-2.png','p-3.png','p-4.png','p-5.png'];
                                    $imgs = array_map(function($fn){ return ['caminho' => 'assets/images/product/' . $fn]; }, $samples);
                                }
                                ?>
                                <div class="bg-warning-subtle p-lg-3 p-2 m-n2 rounded">
                                    <?php if ($imgs): ?>
                                        <div id="equipPhotosCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-bs-pause="false" data-bs-wrap="true">
                                            <div class="carousel-inner rounded bg-light">
                                                <?php foreach ($imgs as $k=>$ax): ?>
                                                    <div class="carousel-item <?php echo $k===0?'active':'' ?>">
                                                        <img src="<?php echo htmlspecialchars($ax['caminho']) ?>" class="d-block w-100" style="object-fit:contain; height:340px" alt="foto">
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <button class="carousel-control-prev" type="button" data-bs-target="#equipPhotosCarousel" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Anterior</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#equipPhotosCarousel" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Próxima</span>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="rounded bg-light d-flex align-items-center justify-content-center" style="height:340px">
                                            <img src="assets/images/product/p-1.png" class="img-fluid rounded" alt="placeholder" />
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <h4 class="mb-2"><?php echo htmlspecialchars($equip['nome'] ?? '') ?></h4>
                                                <p class="mb-1">Tipo: <?php echo htmlspecialchars($equip['tipo'] ?? '') ?></p>
                                                <p class="mb-1">Marca/Modelo: <?php echo htmlspecialchars(trim(($equip['marca']??'').' '.($equip['modelo']??''))) ?></p>
                                                <p class="mb-1">Nº Série: <?php echo htmlspecialchars($equip['numero_serie'] ?? '') ?></p>
                                                <p class="mb-0">Patrimônio: <?php echo htmlspecialchars($equip['patrimonio'] ?? '') ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-borderless mb-0">
                                                        <tbody>
                                                            <tr>
                                                                <td class="p-0 pe-5 py-1"><p class="mb-0 text-dark fw-semibold"> Atualizado em: </p></td>
                                                                <td class="text-end text-dark fw-medium px-0 py-1"><?php $dt = $equip['atualizado_em'] ?? null; if ($dt) { try { $d = new DateTime((string)$dt); $d->setTimezone(new DateTimeZone('America/Sao_Paulo')); echo htmlspecialchars($d->format('d/m/Y H:i')); } catch (Exception $e) { $ts = strtotime($dt); echo $ts ? htmlspecialchars(date('d/m/Y H:i', $ts)) : '-'; } } else { echo '-'; } ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="p-0 pe-5 py-1"><p class="mb-0">Checklist por: </p></td>
                                                                <td class="text-end text-dark fw-medium px-0 py-1"><?php echo htmlspecialchars($cl['updated_by'] ?? '-') ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="p-0 pe-5 py-1"><p class="mb-0">Status Geral: </p></td>
                                                                <td class="text-end px-0 py-1">
                                                                    <?php $allOk = (!empty($cl['av_kaspersky']) && !empty($cl['remote_rustdesk']) && !empty($cl['updates_ok']) && !empty($cl['encryption_ok']) && !empty($cl['backup_ok']) && !empty($cl['asset_tagged']) && empty($cl['scheduled_at'])); ?>
                                                                    <span class="badge bg-<?php echo $allOk?'success':'warning' ?> text-white px-2 py-1 fs-13"><?php echo $allOk?'OK':'Pendente' ?></span>
                                                                </td>
                                                            </tr>
                                                            <?php if (!empty($cl['scheduled_at'])): ?>
                                                            <tr>
                                                                <td class="p-0 pe-5 py-1"><p class="mb-0">Agendado para: </p></td>
                                                                <td class="text-end text-dark fw-medium px-0 py-1"><?php $ts = strtotime($cl['scheduled_at']); $dias = [1=>'Segunda',2=>'Terça',3=>'Quarta',4=>'Quinta',5=>'Sexta',6=>'Sábado',7=>'Domingo']; $dia = $ts ? $dias[(int)date('N',$ts)] : ''; echo htmlspecialchars(($dia? $dia.' - ' : '').str_replace('T',' ', $cl['scheduled_at'])); ?></td>
                                                            </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                

                                <div class="d-flex justify-content-center mt-3 mb-2">
                                    <button class="btn btn-warning btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#equipChecklistSection" aria-expanded="false" aria-controls="equipChecklistSection">Mais informações</button>
                                </div>
                                <div class="collapse" id="equipChecklistSection">
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="table-responsive table-borderless text-nowrap table-centered">
                                            <table class="table mb-0">
                                                <thead class="bg-light bg-opacity-50">
                                                    <tr>
                                                        <th class="border-0 py-2">Item</th>
                                                        <th class="border-0 py-2">Status</th>
                                                        <th class="text-end border-0 py-2">Observações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded bg-light avatar d-flex align-items-center justify-content-center">
                                                                    <iconify-icon icon="solar:shield-check-bold-duotone" class="fs-20"></iconify-icon>
                                                                </div>
                                                                <div>
                                                                    <span class="text-dark fw-medium fs-15">Kaspersky</span>
                                                                    <p class="text-muted mb-0 mt-1 fs-13">Antivírus</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php echo $cl['av_kaspersky']?'<span class="badge bg-success-subtle text-success">OK</span>':'<span class="badge bg-warning-subtle text-warning">Pendente</span>'; ?>
                                                        </td>
                                                        <td class="text-end">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded bg-light avatar d-flex align-items-center justify-content-center">
                                                                    <iconify-icon icon="solar:monitor-bold-duotone" class="fs-20"></iconify-icon>
                                                                </div>
                                                                <div>
                                                                    <span class="text-dark fw-medium fs-15">RustDesk</span>
                                                                    <p class="text-muted mb-0 mt-1 fs-13">Acesso remoto</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php echo $cl['remote_rustdesk']?'<span class="badge bg-success-subtle text-success">OK</span>':'<span class="badge bg-warning-subtle text-warning">Pendente</span>'; ?>
                                                        </td>
                                                        <td class="text-end">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded bg-light avatar d-flex align-items-center justify-content-center">
                                                                    <iconify-icon icon="solar:refresh-bold-duotone" class="fs-20"></iconify-icon>
                                                                </div>
                                                                <div>
                                                                    <span class="text-dark fw-medium fs-15">Atualizações</span>
                                                                    <p class="text-muted mb-0 mt-1 fs-13">Sistema e software</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php echo $cl['updates_ok']?'<span class="badge bg-success-subtle text-success">OK</span>':'<span class="badge bg-warning-subtle text-warning">Pendente</span>'; ?>
                                                        </td>
                                                        <td class="text-end">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded bg-light avatar d-flex align-items-center justify-content-center">
                                                                    <iconify-icon icon="solar:lock-bold-duotone" class="fs-20"></iconify-icon>
                                                                </div>
                                                                <div>
                                                                    <span class="text-dark fw-medium fs-15">Criptografia</span>
                                                                    <p class="text-muted mb-0 mt-1 fs-13">Proteção de disco</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php echo $cl['encryption_ok']?'<span class="badge bg-success-subtle text-success">OK</span>':'<span class="badge bg-warning-subtle text-warning">Pendente</span>'; ?>
                                                        </td>
                                                        <td class="text-end">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded bg-light avatar d-flex align-items-center justify-content-center">
                                                                    <iconify-icon icon="solar:cloud-check-bold-duotone" class="fs-20"></iconify-icon>
                                                                </div>
                                                                <div>
                                                                    <span class="text-dark fw-medium fs-15">Backup</span>
                                                                    <p class="text-muted mb-0 mt-1 fs-13">Cópias de segurança</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php echo $cl['backup_ok']?'<span class="badge bg-success-subtle text-success">OK</span>':'<span class="badge bg-warning-subtle text-warning">Pendente</span>'; ?>
                                                        </td>
                                                        <td class="text-end">&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="rounded bg-light avatar d-flex align-items-center justify-content-center">
                                                                    <iconify-icon icon="solar:tag-horizontal-bold-duotone" class="fs-20"></iconify-icon>
                                                                </div>
                                                                <div>
                                                                    <span class="text-dark fw-medium fs-15">Etiqueta</span>
                                                                    <p class="text-muted mb-0 mt-1 fs-13">Patrimônio / ativo</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <?php echo $cl['asset_tagged']?'<span class="badge bg-success-subtle text-success">OK</span>':'<span class="badge bg-warning-subtle text-warning">Pendente</span>'; ?>
                                                        </td>
                                                        <td class="text-end">&nbsp;</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                </div>

                                <div class="mt-3 mb-1">
                                    <div class="text-end d-print-none">
                                        <a href="javascript:window.print()" class="btn btn-info width-xl">Imprimir</a>
                                        <a href="inventory-received-orders.php" class="btn btn-outline-primary width-xl">Voltar</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'partials/footer.php' ?>
    </div>
    
</div>
<?php include 'partials/vendor-scripts.php' ?>
</body>
</html>
