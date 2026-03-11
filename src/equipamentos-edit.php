<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php require_once 'services/csrf.php'; ?>
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$equip = $id>0 ? equipamentoGet($id) : null;
$erro = null; $ok = null;
if (!$equip) { header('Location: inventario-dashboard.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!csrfValidate($token)) {
        $erro = 'Token inválido';
    } else if (($_POST['action'] ?? '') === 'update_fields') {
        $nome = trim($_POST['nome'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $so = trim($_POST['so'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $cpu = trim($_POST['cpu'] ?? '');
        $ram = trim($_POST['ram'] ?? '');
        $armazenamento = trim($_POST['armazenamento'] ?? '');
        $ram = preg_replace('/\D/', '', $ram);
        $armazenamento = preg_replace('/\D/', '', $armazenamento);
        $fornecedor = trim($_POST['fornecedor'] ?? '');
        $numeroSerie = trim($_POST['numero_serie'] ?? '');
        $localizacao = trim($_POST['localizacao'] ?? '');
        $status = trim($_POST['status'] ?? '');
        $preco = trim($_POST['preco'] ?? '');
        $dataCompra = trim($_POST['data_compra'] ?? '');
        $garantia = trim($_POST['garantia'] ?? '');
        $patrimonio = trim($_POST['patrimonio'] ?? '');
        if ($nome === '' || $categoria === '') {
            $erro = 'Preencha os campos obrigatórios (Nome e Categoria)';
        } else {
            $payload = [
                'nome' => $nome,
                'tipo' => $categoria,
                'marca' => $marca,
                'modelo' => $modelo,
                'especificacoes' => json_encode(['cpu'=>$cpu,'ram'=>$ram,'disco'=>$armazenamento,'os'=>$so,'desc'=>$descricao,'fornec'=>$fornecedor]),
                'numero_serie' => $numeroSerie,
                'patrimonio' => $patrimonio,
                'localizacao' => $localizacao,
                'status' => $status,
                'fornecedor' => $fornecedor,
                'data_aquisicao' => $dataCompra,
                'garantia_fim' => $garantia
            ];
            $precoRaw = $preco;
            if ($precoRaw !== '') {
                $norm = preg_replace('/[^0-9,\.]/','', $precoRaw);
                $norm = str_replace('.', '', $norm);
                $norm = str_replace(',', '.', $norm);
                $payload['valor'] = is_numeric($norm) ? number_format((float)$norm, 2, '.', '') : '0.00';
            }
            if (equipamentoUpdate($id, $payload)) {
                header('Location: inventory-warehouse.php');
                exit;
            } else {
                $erro = 'Falha ao atualizar equipamento';
            }
        }
    } else if (($_POST['action'] ?? '') === 'upload_foto') {
        if (isset($_FILES['foto']) && $_FILES['foto']['error']===UPLOAD_ERR_OK) {
            $orig = $_FILES['foto']['name'];
            $size = (int)$_FILES['foto']['size'];
            $tmp  = $_FILES['foto']['tmp_name'];
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allow = ['jpg','jpeg','png','gif'];
            if (!in_array($ext, $allow)) {
                $erro = 'Tipo de arquivo não permitido';
            } else {
                $dir = __DIR__ . '/assets/uploads/equipamentos/' . $id;
                if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
                $fname = uniqid('foto_', true) . '.' . $ext;
                $dest = $dir . '/' . $fname;
                if (move_uploaded_file($tmp, $dest)) {
                    $rel = 'assets/uploads/equipamentos/' . $id . '/' . $fname;
                    anexoAdd($id, 'foto', $orig, $rel, $size);
                    $ok = 'Foto enviada com sucesso';
                } else {
                    $erro = 'Falha ao salvar arquivo';
                }
            }
        } else {
            $erro = 'Nenhum arquivo enviado';
        }
    }
}
?>
<head>
    <?php $subTitle = 'Editar Equipamento'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>
<body>
<div class="wrapper">
    <?php $subTitle = 'Editar Equipamento'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>
    <div class="page-content">
        <div class="container-xxl">
            <div class="row">
                <div class="col-xl-3 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <?php $anexos = anexosList($id); $imgs = array_values(array_filter($anexos, function($ax){ return preg_match('/\.(jpg|jpeg|png|gif)$/i', $ax['caminho'] ?? ''); })); ?>
                            <?php if ($imgs): ?>
                                <div id="equipEditPhotos" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000" data-bs-pause="hover" data-bs-wrap="true">
                                    <div class="carousel-inner rounded bg-light">
                                        <?php foreach ($imgs as $k=>$ax): ?>
                                        <div class="carousel-item <?php echo $k===0?'active':'' ?>">
                                            <img src="<?php echo htmlspecialchars($ax['caminho']) ?>" class="d-block w-100" style="object-fit:contain;height:300px" alt="foto">
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#equipEditPhotos" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Anterior</span></button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#equipEditPhotos" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Próxima</span></button>
                                </div>
                            <?php else: ?>
                                <img src="assets/images/product/p-1.png" alt="" class="img-fluid rounded bg-light">
                            <?php endif; ?>
                            <div class="mt-3">
                                <form method="post" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                                    <input type="hidden" name="action" value="upload_foto">
                                    <input type="file" name="foto" accept="image/*" class="form-control form-control-sm" required>
                                    <button class="btn btn-sm btn-warning" type="submit">Enviar foto</button>
                                </form>
                                <?php if ($erro): ?><div class="alert alert-danger mt-2 mb-0"><?php echo htmlspecialchars($erro) ?></div><?php endif; ?>
                                <?php if ($ok): ?><div class="alert alert-success mt-2 mb-0"><?php echo htmlspecialchars($ok) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8">
                    <div class="card">
                        <div class="card-header"><h4 class="card-title">Editar Informações</h4></div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                                <input type="hidden" name="action" value="update_fields">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nome do Ativo</label>
                                            <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($equip['nome'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <label class="form-label">Categoria do Ativo</label>
                                        <?php $categorias = []; try { $categorias = categoriasList(); } catch (Exception $e) { $categorias = []; } if (!is_array($categorias)) { $categorias = []; } ?>
                                        <select class="form-control" name="categoria" data-choices required>
                                            <option value="">Selecione</option>
                                            <?php if (empty($categorias)) { foreach(['Desktop','Notebook','Celular','Tablet','Geral'] as $c) { $sel = (($equip['tipo'] ?? '')===$c)?'selected':''; echo '<option value="'.htmlspecialchars($c).'" '.$sel.'>'.htmlspecialchars($c).'</option>'; } } else { foreach ($categorias as $c) { $nome = is_array($c)?($c['nome']??''):(string)$c; if($nome==='') continue; $sel = (($equip['tipo'] ?? '')===$nome)?'selected':''; echo '<option value="'.htmlspecialchars($nome).'" '.$sel.'>'.htmlspecialchars($nome).'</option>'; } } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">Fabricante</label>
                                            <input type="text" name="marca" class="form-control" value="<?php echo htmlspecialchars($equip['marca'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                            <label class="form-label">Modelo</label>
                                            <input type="text" name="modelo" class="form-control" value="<?php echo htmlspecialchars($equip['modelo'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <?php $spec = json_decode((string)($equip['especificacoes'] ?? ''), true) ?: []; ?>
                                        <label class="form-label">Sistema Operacional</label>
                                        <select class="form-control" name="so" data-choices>
                                            <?php $soVal = $spec['os'] ?? ''; ?>
                                            <option value="">Selecione</option>
                                            <?php foreach(['Windows','macOS','Linux','Android','iOS','Outro'] as $opt){ $sel = ($soVal===$opt)?'selected':''; echo '<option value="'.htmlspecialchars($opt).'" '.$sel.'>'.htmlspecialchars($opt).'</option>'; } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-lg-3"><div class="mb-3"><label class="form-label">Processador (CPU)</label><input type="text" name="cpu" class="form-control" value="<?php echo htmlspecialchars($spec['cpu'] ?? '') ?>"></div></div>
                                    <div class="col-lg-3"><div class="mb-3"><label class="form-label">Memória RAM</label><input type="text" inputmode="numeric" pattern="\d+" title="Somente números" name="ram" class="form-control" value="<?php echo htmlspecialchars($spec['ram'] ?? '') ?>" placeholder="Ex.: 16"></div></div>
                                    <div class="col-lg-3"><div class="mb-3"><label class="form-label">Armazenamento</label><input type="text" inputmode="numeric" pattern="\d+" title="Somente números" name="armazenamento" class="form-control" value="<?php echo htmlspecialchars($spec['disco'] ?? '') ?>" placeholder="Ex.: 512"></div></div>
                                    <div class="col-lg-3"><div class="mb-3"><label class="form-label">Fornecedor</label><input type="text" name="fornecedor" class="form-control" value="<?php echo htmlspecialchars($spec['fornec'] ?? '') ?>"></div></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3"><div class="mb-3"><label class="form-label">Número de Série</label><input type="text" name="numero_serie" class="form-control" value="<?php echo htmlspecialchars($equip['numero_serie'] ?? '') ?>"></div></div>
                                    <div class="col-lg-3"><div class="mb-3"><label class="form-label">Localização</label><input type="text" name="localizacao" class="form-control" value="<?php echo htmlspecialchars($equip['localizacao'] ?? '') ?>"></div></div>
                                    <div class="col-lg-3"><label class="form-label">Status</label><select class="form-control" name="status" data-choices><?php foreach(['Em uso','Em estoque','Manutenção','Descartado'] as $st){ $sel=(($equip['status']??'')===$st)?'selected':''; echo '<option value="'.htmlspecialchars($st).'" '.$sel.'>'.htmlspecialchars($st).'</option>'; } ?></select></div>
                                    <div class="col-lg-3"><div class="mb-3"><label class="form-label">Preço</label><input type="text" inputmode="numeric" name="preco" class="form-control" value="<?php echo htmlspecialchars(number_format((float)($equip['valor'] ?? 0), 2, ',', '.')) ?>"></div></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12"><div class="mb-3"><label class="form-label">Descrição</label><textarea class="form-control bg-light-subtle" name="descricao" rows="6"><?php echo htmlspecialchars(($spec['desc'] ?? '')) ?></textarea></div></div>
                                </div>
                                <div class="card"><div class="card-header"><h4 class="card-title">Aquisição</h4></div><div class="card-body"><div class="row"><div class="col-lg-4"><label class="form-label">Data de Compra</label><input type="date" name="data_compra" class="form-control" value="<?php echo htmlspecialchars($equip['data_aquisicao'] ?? '') ?>"></div><div class="col-lg-4"><label class="form-label">Garantia até</label><input type="date" name="garantia" class="form-control" value="<?php echo htmlspecialchars($equip['garantia_fim'] ?? '') ?>"></div><div class="col-lg-4"><label class="form-label">Tag Patrimônio</label><input type="text" name="patrimonio" class="form-control" value="<?php echo htmlspecialchars($equip['patrimonio'] ?? '') ?>"></div></div></div></div>
<div class="p-3 bg-light mb-3 rounded"><div class="row justify-content-end g-2"><div class="col-lg-2"><button type="submit" class="btn btn-outline-secondary w-100">Salvar</button></div><div class="col-lg-2"><a href="inventario-dashboard.php" class="btn btn-primary w-100">Cancelar</a></div></div></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'partials/footer.php' ?>
</div>
</div>
</div>
</div>
<?php include 'partials/vendor-scripts.php' ?>
<script>
(function(){
  var precoInput=document.querySelector('input[name="preco"]');
  function formatNumberBR(digits){
    digits=(digits||'').replace(/\D/g,'');
    var value=parseInt(digits||'0',10)/100;
    return new Intl.NumberFormat('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(value);
  }
  function applyMask(){ if(!precoInput) return; precoInput.value=formatNumberBR(precoInput.value); }
  if(precoInput){ applyMask(); precoInput.addEventListener('input',applyMask); precoInput.addEventListener('blur',applyMask); }
  function digitsOnly(el){ if(!el) return; var fn=function(){ el.value=(el.value||'').replace(/\D/g,''); }; fn(); el.addEventListener('input',fn); el.addEventListener('blur',fn); }
  digitsOnly(document.querySelector('input[name="ram"]'));
  digitsOnly(document.querySelector('input[name="armazenamento"]'));
})();
</script>
</body>
</html>
