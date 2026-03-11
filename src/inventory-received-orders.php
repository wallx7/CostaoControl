<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php require_once 'services/csrf.php'; ?>
<?php
$erro = null; $showModal = false; $editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0; $showSuccess=false; $successMsg='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!csrfValidate($token)) {
        $erro = 'Token inválido';
    } else {
        if (($_POST['action'] ?? '') === 'save_checklist') {
            $equipId = (int)($_POST['equip_id'] ?? 0);
            $uname = $_SESSION['user']['nome'] ?? null;
            $scheduled = trim($_POST['scheduled_at'] ?? '');
            $scheduled = $scheduled !== '' ? $scheduled : null;
            $updatesOk = isset($_POST['updates_ok']) ? 1 : 0;
            if ($scheduled) { $updatesOk = 0; }
            checklistSet($equipId, [
                'av_kaspersky' => isset($_POST['av_kaspersky']) ? 1 : 0,
                'remote_rustdesk' => isset($_POST['remote_rustdesk']) ? 1 : 0,
                'updates_ok' => $updatesOk,
                'encryption_ok' => isset($_POST['encryption_ok']) ? 1 : 0,
                'backup_ok' => isset($_POST['backup_ok']) ? 1 : 0,
                'asset_tagged' => isset($_POST['asset_tagged']) ? 1 : 0,
                'notes' => trim($_POST['notes'] ?? ''),
                'updated_by' => $uname,
                'scheduled_at' => $scheduled,
            ]);
            $uid = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
            logAdd($uid, 'checklist_update', 'equipamentos', $equipId, ['fields'=>['av','remote','updates','enc','backup','tag']]);
            $showSuccess = true; $successMsg = 'Checklist atualizado';
        } elseif (($_POST['action'] ?? '') === 'assign_task') {
            $equipId = (int)($_POST['equip_id'] ?? 0);
            $toId = (int)($_POST['assigned_to'] ?? 0);
            $reason = trim($_POST['assign_reason'] ?? '');
            $note = trim($_POST['assign_note'] ?? '');
            $by = $_SESSION['user']['nome'] ?? null;
            $users = usersList(); $toName = null; foreach ($users as $u){ if((int)$u['id']===$toId){ $toName = $u['nome']; break; } }
            if ($equipId && $toId && $toName) {
                checklistSet($equipId, [
                    'assigned_to' => $toName,
                    'assigned_to_id' => $toId,
                    'assigned_by' => $by,
                    'assign_reason' => $reason !== '' ? $reason : null,
                    'assign_requested_at' => date('c'),
                    'notes' => $note !== '' ? $note : ($by?('Designado por '.$by):''),
                ]);
                $uid = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
                logAdd($uid, 'assign_task', 'equipamentos', $equipId, ['to_id'=>$toId,'to_name'=>$toName,'reason'=>$reason,'note'=>$note]);
            }
            $showSuccess = true; $successMsg = 'Tarefa delegada';
        } elseif (($_POST['action'] ?? '') === 'seed_demo') {
            $payload = $_POST['payload'] ?? '';
            $items = [];
            if ($payload !== '') {
                $arr = json_decode($payload, true);
                if (is_array($arr)) $items = $arr;
            }
            if (count($items) === 0) {
                for ($i=1; $i<=10; $i++) {
                    $items[] = [
                        'nome' => 'Equipamento '.$i,
                        'tipo' => ($i%2===0?'Notebook':'Desktop'),
                        'localizacao' => 'TI',
                        'status' => 'disponivel',
                        'numero_serie' => 'SN'.str_pad((string)rand(10000,99999),5,'0',STR_PAD_LEFT),
                        'patrimonio' => 'PAT'.str_pad((string)$i,3,'0',STR_PAD_LEFT),
                    ];
                }
            }
            foreach ($items as $it) {
                $id = equipamentoAdd($it);
                checklistSet($id, [
                    'updates_ok' => (rand(0,1)==1?1:0),
                    'updated_by' => $_SESSION['user']['nome'] ?? 'Demo',
                ]);
            }
            $showSuccess = true; $successMsg = 'Itens demo adicionados';
        }
    }
}
?>

<head>
     <?php
    $subTitle = "Bancada Virtual";
    include 'partials/title-meta.php'; ?>

       <?php include 'partials/head-css.php' ?>
</head>

<body>

     <!-- START Wrapper -->
     <div class="wrapper">

          <?php 
    $subTitle = "Bancada Virtual";
    include 'partials/topbar.php'; ?>
<?php include 'partials/main-nav.php'; ?>

          <!-- ==================================================== -->
          <!-- Start right Content here -->
          <!-- ==================================================== -->
          <div class="page-content">

               <!-- Start Container Fluid -->
               <div class="container-xxl">

                    <div class="row">
                         <?php $items = equipamentosList(); $total=is_array($items)?count($items):0; $ok=0; $pend=0; foreach($items as $e){ $cl=checklistGet($e['id']); $allOk=($cl['av_kaspersky']&&$cl['remote_rustdesk']&&$cl['updates_ok']&&$cl['encryption_ok']&&$cl['backup_ok']&&$cl['asset_tagged']); if($allOk) $ok++; else $pend++; } ?>
                         <div class="col-md-6 col-xl-3">
                              <div class="card">
                                   <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                             <div>
                                                  <h4 class="card-title mb-2">Checklists Pendentes</h4>
                                                  <p class="text-muted fw-medium fs-22 mb-0"><?php echo $pend ?></p>
                                             </div>
                                             <div>
                                                  <div class="avatar-md bg-primary bg-opacity-10 rounded">
                                                       <iconify-icon icon="solar:clipboard-remove-broken" class="fs-32 text-primary avatar-title"></iconify-icon>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>
                         <div class="col-md-6 col-xl-3">
                              <div class="card">
                                   <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                             <div>
                                                  <h4 class="card-title mb-2">Checklists OK</h4>
                                                  <p class="text-muted fw-medium fs-22 mb-0"><?php echo $ok ?></p>
                                             </div>
                                             <div>
                                                  <div class="avatar-md bg-primary bg-opacity-10 rounded">
                                                       <iconify-icon icon="solar:clock-circle-broken" class="fs-32 text-primary avatar-title"></iconify-icon>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>

                         <div class="col-md-6 col-xl-3">
                              <div class="card">
                                   <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                             <div>
                                                  <h4 class="card-title mb-2">Atualizados Hoje</h4>
                                                  <p class="text-muted fw-medium fs-22 mb-0"><?php echo count(array_filter($items,function($e){ $cl=checklistGet($e['id']); return isset($cl['updated_at']) && substr($cl['updated_at'],0,10)===date('Y-m-d'); })) ?></p>
                                             </div>
                                             <div>
                                                  <div class="avatar-md bg-primary bg-opacity-10 rounded">
                                                       <iconify-icon icon="solar:clipboard-check-broken" class="fs-32 text-primary avatar-title"></iconify-icon>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>

                         <div class="col-md-6 col-xl-3">
                              <div class="card">
                                   <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                             <div>
                                                  <h4 class="card-title mb-2">Total de Equipamentos</h4>
                                                  <p class="text-muted fw-medium fs-22 mb-0"><?php echo $total ?></p>
                                             </div>
                                             <div>
                                                  <div class="avatar-md bg-primary bg-opacity-10 rounded">
                                                       <iconify-icon icon="solar:inbox-line-broken" class="fs-32 text-primary avatar-title"></iconify-icon>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>

                    </div>

                    <div class="row">
                         <div class="col-xl-12">
                              <div class="card">
                                   <div class="d-flex card-header justify-content-between align-items-center">
                                        <div>
                                             <h4 class="card-title">Bancada Virtual</h4>
                                        </div>
                                        <div class="dropdown d-flex align-items-center gap-2">
                                             <form method="post" id="seedForm">
                                                  <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                                                  <input type="hidden" name="action" value="seed_demo">
                                                  <input type="hidden" name="payload" id="seedPayload">
                                             </form>
                                             <button type="button" class="btn btn-sm btn-primary" id="btnSeedDemo">Popular 10 itens</button>
                                             <form class="d-flex gap-2" method="get">
                                                  <input type="text" name="q" class="form-control form-control-sm" placeholder="Buscar por nome, série ou patrimônio" value="<?php echo htmlspecialchars($_GET['q'] ?? '') ?>">
                                                  <select name="status" class="form-select form-select-sm">
                                                       <option value="">Todos</option>
                                                       <option value="pendente" <?php echo (($_GET['status'] ?? '')==='pendente')?'selected':''; ?>>Pendentes</option>
                                                       <option value="ok" <?php echo (($_GET['status'] ?? '')==='ok')?'selected':''; ?>>OK</option>
                                                  </select>
                                                  <button class="btn btn-sm btn-outline-light rounded" type="submit">Filtrar</button>
                                             </form>
                                        </div>
                                   </div>

                                   <div>
                                        <div class="table-responsive">
                                             <table class="table align-middle mb-0 table-hover table-centered">
                                                  <thead class="bg-light-subtle">
                                                       <tr>
                                                            <th>Equipamento</th>
                                                            <th>Atualizações</th>
                                                            <th>Atualizado em</th>
                                                            <th>Usuário</th>
                                                       </tr>
                                                  </thead>
                                                  <tbody>
                                                       <?php $q = trim($_GET['q'] ?? ''); $stF = trim($_GET['status'] ?? ''); $list = $items; if ($q!==''){ $list = array_values(array_filter($list,function($r) use($q){ $hay=strtolower(($r['nome']??'').' '.($r['numero_serie']??'').' '.($r['patrimonio']??'')); return str_contains($hay, strtolower($q)); })); } if ($stF!==''){ $list = array_values(array_filter($list,function($r) use($stF){ $cl=checklistGet($r['id']); $isOk = empty($cl['scheduled_at']) && ((int)($cl['updates_ok'] ?? 0)===1); return $stF==='ok'? $isOk : !$isOk; })); } ?>
                                                       <?php foreach ($list as $r): $cl=checklistGet($r['id']); ?>
                                                       <tr>
                                                            <td><?php echo htmlspecialchars($r['nome'] ?? '') ?></td>
                                                            <td><?php $sched = $cl['scheduled_at'] ?? null; if (!empty($sched)) { $ts = strtotime($sched); $dias = [1=>'Segunda',2=>'Terça',3=>'Quarta',4=>'Quinta',5=>'Sexta',6=>'Sábado',7=>'Domingo']; $dia = $ts ? $dias[(int)date('N',$ts)] : 'Agendado'; echo '<span class="badge bg-info-subtle text-info">Agendado para '.$dia.'</span>'; } else { $ok = ((int)($cl['updates_ok'] ?? 0)===1); echo $ok?'<span class="badge bg-success-subtle text-success">OK</span>':'<span class="badge bg-warning-subtle text-warning">Pendente</span>'; } ?></td>
                                                            <td><?php echo htmlspecialchars($cl['updated_at'] ?? '') ?></td>
                                                            <td class="pe-4">
                                                                 <div class="d-flex align-items-center justify-content-between">
                                                                      <span><?php echo htmlspecialchars(($cl['assigned_to'] ?? '') !== '' ? $cl['assigned_to'] : ($cl['updated_by'] ?? '')) ?></span>
                                                                      <div class="d-flex align-items-center">
                                                                           <a href="#" class="btn btn-soft-info btn-sm p-0 me-2 assign-btn" data-equip-id="<?php echo (int)$r['id'] ?>" title="Compartilhar/Delegar">
                                                                                <i class="bx bx-share-alt align-middle fs-16"></i>
                                                                           </a>
                                                                           <a href="inventory-received-orders.php?edit=<?php echo (int)$r['id'] ?>#edit" class="btn btn-soft-primary btn-sm p-0 me-2" title="Editar/Agendar">
                                                                               <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-16"></iconify-icon>
                                                                           </a>
                                                                           <a href="equipamentos-view.php?id=<?php echo (int)$r['id'] ?>" class="btn btn-soft-warning btn-sm p-0" title="Visualizar">
                                                                                <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                                                                           </a>
                                                                      </div>
                                                                 </div>
                                                            </td>
                                                       </tr>
                                                       <?php endforeach; ?>

                                                       
                                                  </tbody>
                                             </table>
                                        </div>
                                        <!-- end table-responsive -->
                                   </div>
                                   <div class="card-footer border-top">
                                        <nav aria-label="Page navigation example">
                                             <ul class="pagination justify-content-end mb-0">
                                                  <li class="page-item"><a class="page-link" href="javascript:void(0);">Anterior</a></li>
                                                  <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                                                  <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                  <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                                                  <li class="page-item"><a class="page-link" href="javascript:void(0);">Próximo</a></li>
                                             </ul>
                                        </nav>
                                   </div>
                              </div>
                         </div>

                    </div>


               </div>
               <!-- End Container Fluid -->

                 <?php include 'partials/footer.php' ?>

          </div>
          <!-- ==================================================== -->
          <!-- End Page Content -->
          <!-- ==================================================== -->

     </div>
     <!-- END Wrapper -->

       <?php include 'partials/vendor-scripts.php' ?>
       <?php $clEdit = $editId ? checklistGet($editId) : checklistDefaults(); ?>
       <div class="modal fade" id="actionSuccess" tabindex="-1" aria-hidden="true">
           <div class="modal-dialog modal-sm modal-dialog-centered">
               <div class="modal-content">
                   <div class="modal-body text-center p-4">
                       <div class="d-flex flex-column align-items-center">
                           <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:72px;height:72px">
                               <i class="bx bx-check checkmark" style="font-size:42px"></i>
                           </div>
                           <div class="mt-3 fw-semibold"><?php echo htmlspecialchars($successMsg ?: 'Sucesso') ?></div>
                           <button type="button" class="btn btn-success mt-3" data-bs-dismiss="modal">OK</button>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       <style>
       @keyframes popIn{0%{transform:scale(0.6);opacity:0}60%{transform:scale(1.1);opacity:1}100%{transform:scale(1);opacity:1}}
       #actionSuccess .checkmark.animate{animation:popIn .4s ease-out}
       </style>
       <?php if ($showSuccess): ?>
       <script>
       document.addEventListener('DOMContentLoaded',function(){var m=new bootstrap.Modal(document.getElementById('actionSuccess'));m.show();var ic=document.querySelector('#actionSuccess .checkmark');if(ic){ic.classList.add('animate');}});
       </script>
       <?php endif; ?>
       <div class="modal fade" id="modalAssign" tabindex="-1" aria-hidden="true">
           <div class="modal-dialog">
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title">Compartilhar/Delegar tarefa</h5>
                       <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                   <form method="post">
                       <div class="modal-body">
                           <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                           <input type="hidden" name="action" value="assign_task">
                           <input type="hidden" name="equip_id" id="assign-equip-id" value="0">
                           <div class="mb-3">
                               <label class="form-label">Responsável</label>
                               <select class="form-select" name="assigned_to" required>
                                   <option value="">Selecione um usuário</option>
                                   <?php foreach (usersList() as $u): ?>
                                       <option value="<?php echo (int)$u['id'] ?>"><?php echo htmlspecialchars($u['nome']).($u['email']?(' - '.htmlspecialchars($u['email'])):'') ?></option>
                                   <?php endforeach; ?>
                               </select>
                           </div>
                           <div class="mb-3">
                               <label class="form-label">Motivo</label>
                               <select class="form-select" name="assign_reason">
                                   <option value="">Selecione um motivo</option>
                                   <option value="Atualizações pendentes">Atualizações pendentes</option>
                                   <option value="Manutenção preventiva">Manutenção preventiva</option>
                                   <option value="Configuração de software">Configuração de software</option>
                                   <option value="Verificação de antivírus">Verificação de antivírus</option>
                                   <option value="Backup e recuperação">Backup e recuperação</option>
                                   <option value="Troca de equipamento">Troca de equipamento</option>
                               </select>
                           </div>
                           <div class="mb-3">
                               <label class="form-label">Observação</label>
                               <textarea class="form-control" name="assign_note" rows="2" placeholder="Detalhes ou pedido"></textarea>
                           </div>
                       </div>
                       <div class="modal-footer">
                           <button type="submit" class="btn btn-primary">Enviar</button>
                       </div>
                   </form>
               </div>
           </div>
       </div>
       <script>
       document.addEventListener('click', function(e){ var btn = e.target.closest('.assign-btn'); if(!btn) return; e.preventDefault(); var id = btn.getAttribute('data-equip-id'); var inp = document.getElementById('assign-equip-id'); if(inp) inp.value = id; if(window.bootstrap){ var m = new bootstrap.Modal(document.getElementById('modalAssign')); m.show(); }
       });
       </script>
       <div class="modal fade" id="modalEditChecklist" tabindex="-1" aria-hidden="true">
           <div class="modal-dialog">
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title">Editar/Agendar Checklist</h5>
                       <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                   <form method="post">
                       <div class="modal-body">
                           <input type="hidden" name="csrf" value="<?php echo csrfToken() ?>">
                           <input type="hidden" name="action" value="save_checklist">
                           <input type="hidden" name="equip_id" value="<?php echo (int)$editId ?>">
                           <div class="form-check mb-2">
                               <input class="form-check-input" type="checkbox" id="ck1" name="av_kaspersky" <?php echo $clEdit['av_kaspersky']? 'checked':''; ?>>
                               <label class="form-check-label" for="ck1">Kaspersky OK</label>
                           </div>
                           <div class="form-check mb-2">
                               <input class="form-check-input" type="checkbox" id="ck2" name="remote_rustdesk" <?php echo $clEdit['remote_rustdesk']? 'checked':''; ?>>
                               <label class="form-check-label" for="ck2">RustDesk OK</label>
                           </div>
                           <div class="form-check mb-2">
                               <input class="form-check-input" type="checkbox" id="ck3" name="updates_ok" <?php echo $clEdit['updates_ok']? 'checked':''; ?>>
                               <label class="form-check-label" for="ck3">Atualizações OK</label>
                           </div>
                           <div class="form-check mb-2">
                               <input class="form-check-input" type="checkbox" id="ck4" name="encryption_ok" <?php echo $clEdit['encryption_ok']? 'checked':''; ?>>
                               <label class="form-check-label" for="ck4">Criptografia OK</label>
                           </div>
                           <div class="form-check mb-2">
                               <input class="form-check-input" type="checkbox" id="ck5" name="backup_ok" <?php echo $clEdit['backup_ok']? 'checked':''; ?>>
                               <label class="form-check-label" for="ck5">Backup OK</label>
                           </div>
                           <div class="form-check mb-3">
                               <input class="form-check-input" type="checkbox" id="ck6" name="asset_tagged" <?php echo $clEdit['asset_tagged']? 'checked':''; ?>>
                               <label class="form-check-label" for="ck6">Etiqueta OK</label>
                           </div>
                           <div class="mb-3">
                               <label class="form-label">Agendar Checklist</label>
                               <input type="datetime-local" class="form-control" name="scheduled_at" value="<?php echo $clEdit['scheduled_at']? str_replace(' ', 'T', substr($clEdit['scheduled_at'],0,16)) : '' ?>">
                           </div>
                           <div class="mb-2">
                               <label class="form-label">Observações</label>
                               <textarea class="form-control" name="notes" rows="2"><?php echo htmlspecialchars($clEdit['notes'] ?? '') ?></textarea>
                           </div>
                       </div>
                       <div class="modal-footer">
                           <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                           <button type="submit" class="btn btn-primary">Salvar</button>
                       </div>
                   </form>
               </div>
           </div>
       </div>
       <script>
       (function(){
          var eid = <?php echo (int)$editId ?>;
          if (eid > 0) {
            var m = new bootstrap.Modal(document.getElementById('modalEditChecklist'));
            m.show();
          }
       })();
       </script>
       <script>
       (function(){
           var btn = document.getElementById('btnSeedDemo');
           if (!btn) return;
           btn.addEventListener('click', function(){
               var items = [];
               for (var i=1;i<=10;i++){
                   items.push({
                       nome: 'Equipamento '+String(i).padStart(2,'0'),
                       tipo: (i%2===0?'Notebook':'Desktop'),
                       localizacao: 'TI',
                       status: 'disponivel',
                       numero_serie: 'SN'+(Math.floor(Math.random()*90000)+10000),
                       patrimonio: 'PAT'+String(i).padStart(3,'0')
                   });
               }
               try { localStorage.setItem('cc_seed_items', JSON.stringify(items)); } catch(e) {}
               var inp = document.getElementById('seedPayload');
               inp.value = JSON.stringify(items);
               document.getElementById('seedForm').submit();
           });
       })();
       </script>

</body>

</html>
