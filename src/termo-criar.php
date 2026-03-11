<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php require_once 'services/csrf.php'; ?>
<?php require_once 'services/validator.php'; ?>
<?php
$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!csrfValidate($token)) {
        $erro = 'Token inválido';
    } else {
        $tipo = trim($_POST['tipo'] ?? 'Entrega');
        $equipId = (int)($_POST['equip_id'] ?? 0);
        $equip = null; foreach (equipamentosList() as $e) { if ($e['id']===$equipId) { $equip=$e; break; } }
        $colab_nome = trim($_POST['colab_nome'] ?? '');
        $colab_doc = trim($_POST['colab_doc'] ?? '');
        $colab_email = trim($_POST['colab_email'] ?? '');
        $local = trim($_POST['local'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $prevista = trim($_POST['data_prevista'] ?? '');
        if (!$equip || $colab_nome==='') {
            $erro = 'Selecione o equipamento e informe o nome do colaborador';
        } elseif (!cpfValido($colab_doc)) {
            $erro = 'CPF inválido';
        } else {
            $id = termoAdd($tipo, $equipId, $colab_nome, $observacoes);
            $uid = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
            logActivity('create', 'termo', (int)$id, 'tipo=' . $tipo . ', equipamento=' . (isset($equip['nome']) ? $equip['nome'] : '') . ', colaborador=' . $colab_nome);
            header('Location: termo-assinar.php?id='.$id);
            exit;
        }
    }
}
$equipList = equipamentosList();
$prefEquip = (int)($_GET['equip_id'] ?? 0);
$colabList = colaboradoresList();
?>
<head>
    <?php $subTitle = 'Novo Termo'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>
<body>
<div class="wrapper">
    <?php $subTitle = 'Novo Termo'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>
    <div class="page-content">
        <div class="container-xxl">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center gap-2"><h4 class="card-title m-0">Criar Termo</h4><a href="termos-list.php" class="btn btn-sm btn-outline-secondary">Voltar</a></div>
                        <div class="card-body">
                            <?php if ($erro): ?><div class="alert alert-danger"><?php echo htmlspecialchars($erro) ?></div><?php endif; ?>
                            <form method="post">
                                <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Tipo</label>
                                        <select class="form-select" name="tipo">
                                            <option value="Entrega">Entrega</option>
                                            <option value="Devolução">Devolução</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Equipamento</label>
                                        <select class="form-select" name="equip_id" required>
                                            <option value="">Selecione</option>
                                            <?php foreach ($equipList as $e): ?>
                                                <?php $sel = ($prefEquip && ((int)$e['id'] === $prefEquip)) ? 'selected' : ''; ?>
                                                <option value="<?php echo (int)$e['id'] ?>" <?php echo $sel; ?>>#<?php echo (int)$e['id'] ?> - <?php echo htmlspecialchars($e['nome']) ?> (<?php echo htmlspecialchars($e['tipo']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Colaborador</label>
                                        <select class="form-select" name="colab_nome" required>
                                            <option value="">Selecione</option>
                                            <?php foreach ($colabList as $c): ?>
                                                <option value="<?php echo htmlspecialchars($c['nome']) ?>"><?php echo htmlspecialchars($c['nome']) ?> (<?php echo htmlspecialchars($c['email'] ?? '') ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">CPF</label>
                                        <input class="form-control" name="colab_doc" placeholder="000.000.000-00" inputmode="numeric" maxlength="14" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Email</label>
                                        <input class="form-control" type="email" name="colab_email">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Idade</label>
                                        <input class="form-control" name="colab_idade" placeholder="Ex.: 32" inputmode="numeric">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Local</label>
                                        <input class="form-control" name="local" placeholder="Ex.: Escritório TI / Sala 3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Data Prevista (se aplicável)</label>
                                        <input class="form-control" type="date" name="data_prevista">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Observações</label>
                                        <textarea class="form-control" name="observacoes" rows="4"></textarea>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex gap-2 justify-content-end">
                                    <button class="btn btn-primary" type="submit">Gerar Termo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'partials/footer.php' ?>
        </div>
    </div>
</div>
<?php include 'partials/vendor-scripts.php' ?>
<script>
const cpfInput=document.querySelector('input[name="colab_doc"]');
function maskCPF(v){v=v.replace(/\D/g,'');v=v.slice(0,11);let r='';if(v.length>0)r+=v.substring(0,3);if(v.length>3)r+='.'+v.substring(3,6);if(v.length>6)r+='.'+v.substring(6,9);if(v.length>9)r+='-'+v.substring(9,11);return r;}
if(cpfInput){cpfInput.addEventListener('input',function(){this.value=maskCPF(this.value);});}
</script>
</body>
</html>
