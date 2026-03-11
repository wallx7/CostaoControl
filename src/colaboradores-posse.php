<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php require_once 'services/csrf.php'; ?>
<?php
    $msg = null; $erro = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_colab') {
        $token = $_POST['csrf'] ?? '';
        if (!csrfValidate($token)) {
            $erro = 'Token inválido';
        } else {
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $cpf = trim($_POST['cpf'] ?? '');
            $departamento = trim($_POST['departamento'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');
            if ($nome === '') {
                $erro = 'Informe o nome do colaborador';
            } else {
                if (colaboradorAdd($nome, $email, $cpf, $departamento, $cargo, 1)) {
                    $msg = 'Colaborador adicionado com sucesso';
                } else {
                    $erro = 'Falha ao adicionar colaborador';
                }
            }
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_colab') {
        $token = $_POST['csrf'] ?? '';
        if (!csrfValidate($token)) { $erro = 'Token inválido'; }
        else {
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            if ($id<=0 || $nome==='') { $erro = 'Informe o nome'; }
            else { if (colaboradorUpdate($id, ['nome'=>$nome])) { $msg = 'Colaborador atualizado'; } else { $erro = 'Falha ao atualizar'; } }
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove_item') {
        $token = $_POST['csrf'] ?? '';
        if (!csrfValidate($token)) { $erro = 'Token inválido'; }
        else {
            $equipId = (int)($_POST['equip_id'] ?? 0);
            $colabNome = trim($_POST['colab_nome'] ?? '');
            if ($equipId<=0 || $colabNome==='') { $erro = 'Dados inválidos'; }
            else {
                $tid = termoDevolucaoCriar($equipId, $colabNome);
                if ($tid>0) { header('Location: termo-assinar.php?id=' . (int)$tid); exit; }
                else { $erro = 'Falha ao gerar termo de devolução'; }
            }
        }
    }
?>

<head>
    <?php $subTitle = 'Colaboradores em Posse'; include 'partials/title-meta.php'; ?>
    <?php include 'partials/head-css.php' ?>
</head>

<body>
<div class="wrapper">
    <?php $subTitle = 'Colaboradores em Posse'; include 'partials/topbar.php'; ?>
    <?php include 'partials/main-nav.php'; ?>

    <div class="page-content">
        <div class="container-xxl">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title">Colaboradores e itens em posse</h4>
                        </div>
                        <div class="card-body">
                            <?php $termos = termosList(); $equip = []; foreach(equipamentosList() as $e){ $equip[$e['id']]=$e; }
                            $lastByEquip = [];
                            foreach ($termos as $t){ if (($t['status'] ?? '')!=='assinado') continue; $eid = (int)($t['equip_id'] ?? 0); if($eid<=0) continue; if(isset($lastByEquip[$eid])) continue; $lastByEquip[$eid] = $t; }
                            $map = [];
                            foreach ($lastByEquip as $t){ if (strtolower($t['tipo'] ?? '') !== 'devolução' && strtolower($t['tipo'] ?? '') !== 'devolucao'){ $c = trim($t['colab_nome'] ?? ''); if($c==='') $c='Sem nome'; if(!isset($map[$c])) $map[$c]=[]; $map[$c][] = (int)($t['equip_id'] ?? 0); } }
                            $colabs = colaboradoresList();
                            ?>
                            <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg) ?></div><?php endif; ?>
                            <?php if ($erro): ?><div class="alert alert-danger"><?php echo htmlspecialchars($erro) ?></div><?php endif; ?>
                            <form class="row g-2 mb-3" method="post">
                                <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                                <input type="hidden" name="action" value="add_colab">
                                <div class="col-md-3"><input class="form-control" name="nome" placeholder="Nome" required></div>
                                <div class="col-md-3"><input class="form-control" type="email" name="email" placeholder="Email"></div>
                                <div class="col-md-2"><input class="form-control" name="cpf" placeholder="CPF"></div>
                                <div class="col-md-2"><input class="form-control" name="departamento" placeholder="Célula"></div>
                                <div class="col-md-2"><input class="form-control" name="cargo" placeholder="Cargo"></div>
                                <div class="col-12 text-end"><button class="btn btn-primary btn-sm" type="submit">Adicionar Colaborador</button></div>
                            </form>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-hover table-centered">
                                    <thead class="bg-light-subtle">
                                        <tr>
                                            <th>Colaborador</th>
                                            <th>Itens em posse</th>
                                            <th>Detalhes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($colabs)): ?>
                                            <tr><td colspan="3" class="text-muted">Nenhum colaborador cadastrado</td></tr>
                                        <?php else: foreach ($colabs as $c): $cid = (int)($c['id'] ?? 0); $nome = trim($c['nome'] ?? ''); $ids = $map[$nome] ?? []; $count = is_array($ids) ? count($ids) : 0; ?>
                                            <tr>
                                                <td>
                                                    <div id="view-<?php echo $cid ?>" class="d-flex gap-2 align-items-center">
                                                        <span class="fw-medium"><?php echo htmlspecialchars($nome) ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo (int)$count ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-soft-primary edit-btn" data-id="<?php echo $cid ?>" data-nome="<?php echo htmlspecialchars($nome) ?>" data-ids="<?php echo implode(',', array_map('intval', $ids)) ?>">
                                                        <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                                        Editar
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'partials/footer.php' ?>
        </div>
    </div>
</div>
 <div class="modal fade" id="editColabModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Editar Colaborador</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <form method="post" id="editColabForm" class="modal-body">
         <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
         <input type="hidden" name="action" value="edit_colab">
         <input type="hidden" name="id" id="editColabId" value="">
         <div class="mb-3">
           <label class="form-label">Nome</label>
           <input class="form-control" name="nome" id="editColabNome" placeholder="Nome do colaborador" required>
         </div>
         <hr/>
         <h6 class="mb-2">Itens em posse</h6>
         <div id="editColabItems" class="d-grid gap-2"></div>
         <div class="modal-footer">
           <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
           <button type="submit" class="btn btn-primary">Salvar</button>
         </div>
       </form>
     </div>
   </div>
 </div>
 <?php include 'partials/vendor-scripts.php' ?>
 <script>
 (function(){
    window.EQUIP_NAMES = <?php 
      $nm=[]; foreach($equip as $id=>$e){ $nm[(int)$id] = $e['nome'] ?? ('Equipamento #'.$id); }
      echo json_encode($nm, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
    var modalEl = document.getElementById('editColabModal');
    document.querySelectorAll('.edit-btn').forEach(function(b){
      b.addEventListener('click', function(){
        var id = this.getAttribute('data-id');
        var nome = this.getAttribute('data-nome') || '';
        var idsStr = this.getAttribute('data-ids') || '';
        var ids = idsStr ? idsStr.split(',').filter(function(x){ return x!==''; }) : [];
        document.getElementById('editColabId').value = id;
        document.getElementById('editColabNome').value = nome;
        var list = document.getElementById('editColabItems');
        list.innerHTML = '';
        ids.forEach(function(eid){
          var nm = (window.EQUIP_NAMES||{})[eid] || ('Equipamento #' + eid);
          var form = document.createElement('form');
          form.method = 'post';
          form.className = 'd-flex justify-content-between align-items-center p-2 border rounded';
          form.innerHTML = '<span>'+ nm +'</span>'+
            '<input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">'+
            '<input type="hidden" name="action" value="remove_item">'+
            '<input type="hidden" name="equip_id" value="'+ eid +'">'+
            '<input type="hidden" name="colab_nome" value="'+ nome +'">'+
            '<button class="btn btn-sm btn-soft-danger" type="submit">Gerar Devolução</button>';
          list.appendChild(form);
        });
        var m = new bootstrap.Modal(modalEl);
        m.show();
      });
    });
  })();
 </script>
 </body>
 </html>
