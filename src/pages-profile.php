<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>
<?php require_once 'services/csrf.php'; ?>
<?php
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='upload_avatar') {
    $tok = $_POST['csrf_token'] ?? '';
    if (!csrfValidate($tok)) {
        $msg = 'Token inválido.';
    } else {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error']===UPLOAD_ERR_OK) {
            $uid = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;
            $orig = $_FILES['avatar']['name'];
            $tmp  = $_FILES['avatar']['tmp_name'];
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allow = ['jpg','jpeg','png'];
            if (!in_array($ext, $allow)) {
                $msg = 'Formato inválido.';
            } else {
                $dir = __DIR__ . '/assets/uploads/users/' . ($uid?:'0');
                if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
                $fname = 'avatar.' . $ext;
                $dest = $dir . '/' . $fname;
                if (move_uploaded_file($tmp, $dest)) {
                    $rel = 'assets/uploads/users/' . ($uid?:'0') . '/' . $fname;
                    $mime = ($ext==='png') ? 'image/png' : 'image/jpeg';
                    try {
                        banco_storage_ensure_bucket('avatars');
                        banco_storage_upload('avatars', 'user-' . ($uid?:'0') . '/' . $fname, $dest, $mime);
                        $pub = banco_storage_public_url('avatars', 'user-' . ($uid?:'0') . '/' . $fname);
                        $_SESSION['user']['avatar'] = $pub;
                        banco_update('usuarios', ['id'=>'eq.' . ($uid?:'0')], ['avatar'=>$pub]);
                    } catch (Exception $e) {
                        $_SESSION['user']['avatar'] = $rel;
                    }
                    logActivity('user_avatar_update', 'usuario', $uid, 'Avatar atualizado: ' . $orig);
                    $msg = 'Foto atualizada.';
                } else {
                    $msg = 'Falha ao salvar.';
                }
            }
        } else {
            $msg = 'Nenhum arquivo enviado.';
        }
    }
}
?>

<head>
     <?php
    $subTitle = "Perfil";
    include 'partials/title-meta.php'; ?>

      <?php include 'partials/head-css.php' ?>
      <style>
      .avatar-wrapper .avatar-overlay{display:none}
      .avatar-wrapper:hover .avatar-overlay{display:flex}
      </style>
</head>

<body>

     <!-- START Wrapper -->
     <div class="wrapper">

          <?php 
    $subTitle = "Perfil";
    include 'partials/topbar.php'; ?>
<?php include 'partials/main-nav.php'; ?>

          <!-- ==================================================== -->
          <!-- Start right Content here -->
          <!-- ==================================================== -->
          <div class="page-content">

               <!-- Start Container xxl -->
               <div class="container-xxl">

                    <div class="row justify-content-center">
                         <div class="col-xl-8 col-lg-10">
                              <div class="card overflow-hidden">
                                   <div class="card-body">
                                        <div class="profile-bg rounded-top position-relative mx-n3 mt-n3" style="background-image:url('assets/images/fundobanner.png'); background-size:cover; background-position:center; height:200px">
                                             <?php
                                             $u = $_SESSION['user'] ?? ['nome'=>'Usuário','papel'=>'admin'];
                                             $avatar = isset($u['avatar']) ? (string)$u['avatar'] : '';
                                             $defaultAv = 'assets/images/costaopadrao.jpg';
                                             if ($avatar !== '' && preg_match('#^https?://#', $avatar)) {
                                                 
                                             } else {
                                                 $avatar = ($avatar !== '' && file_exists(__DIR__ . '/' . $avatar)) ? $avatar : $defaultAv;
                                             }
                                             ?>
                                             <div class="position-absolute top-100 start-0 translate-middle ms-5">
                                                 <div class="avatar-wrapper position-relative" style="width:6rem; height:6rem;">
                                                     <img src="<?php echo htmlspecialchars($avatar) ?>" alt="avatar" class="w-100 h-100 border border-light border-3 rounded-circle" onerror="this.src='assets/images/costaopadrao.jpg'">
                                                     <form id="avatarForm" method="post" enctype="multipart/form-data" class="d-none">
                                                         <input type="hidden" name="csrf_token" value="<?php echo csrfToken() ?>">
                                                         <input type="hidden" name="action" value="upload_avatar">
                                                         <input type="file" name="avatar" id="avatarFile" accept=".jpg,.jpeg,.png">
                                                     </form>
                                                     <label for="avatarFile" class="avatar-overlay rounded-circle text-white fw-semibold align-items-center justify-content-center" style="position:absolute; inset:0; background:rgba(0,0,0,.45); cursor:pointer;">Mudar foto</label>
                                                     </div>
                                             </div>
                                             <script>
                                             (function(){
                                                var f=document.getElementById('avatarFile');
                                                if(f){ f.classList.add('d-none'); f.addEventListener('change',function(){ if(this.files&&this.files[0]){ var form=document.getElementById('avatarForm'); if(form) form.submit(); } }); }
                                             })();
                                             </script>
                                        </div>
                                        <div class="mt-5 d-flex flex-wrap align-items-center justify-content-between">
                                             <div>
                                                  <?php $u = $_SESSION['user'] ?? ['nome'=>'Usuário','papel'=>'admin']; ?>
                                                  <h4 class="mb-1"><?php echo htmlspecialchars($u['nome'] ?? 'Usuário') ?> <i class='bx bxs-badge-check text-success align-middle'></i></h4>
                                                  <p class="mb-0"><?php echo (($u['papel'] ?? 'admin')==='admin')?'Administrador':'Usuário' ?></p>
                                             </div>
                                            
                                        </div>
                                        

                                   </div>
                              </div>
                         </div>

                    </div>

                    <?php
                    $uid = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;
                    $equipAll = equipamentosList();
                    $tasks = [];
                    $countAtribuidas = 0;
                    $countAgendadas = 0;
                    $countConcluidas = 0;
                    foreach ($equipAll as $e) {
                        $cid = (int)($e['id'] ?? 0);
                        if ($cid <= 0) { continue; }
                        $cl = checklistGet($cid);
                        $aid = (int)($cl['assigned_to_id'] ?? 0);
                        if ($aid === $uid) {
                            $status = $cl['status_final'] ?? 'Pendente';
                            $countAtribuidas++;
                            if (!empty($cl['scheduled_at']) || $status === 'Agendado') { $countAgendadas++; }
                            if ($status === 'OK') { $countConcluidas++; }
                            $tasks[] = [
                                'equip_id' => $cid,
                                'equip_nome' => $e['nome'] ?? '',
                                'tipo' => $e['tipo'] ?? '',
                                'status' => $status,
                                'reason' => $cl['assign_reason'] ?? '',
                                'by' => $cl['assigned_by'] ?? '',
                                'when' => $cl['assign_requested_at'] ?? null
                            ];
                        }
                    }
                    usort($tasks, function($a,$b){ $ta = strtotime($a['when'] ?? ''); $tb = strtotime($b['when'] ?? ''); return $tb <=> $ta; });
                    $fmt = function($s){ if(!$s) return ''; try{ $dt=new DateTime((string)$s, new DateTimeZone('UTC')); $dt->setTimezone(new DateTimeZone('America/Sao_Paulo')); return $dt->format('d/m/Y \à\s H:i'); }catch(Exception $e){ $ts=strtotime((string)$s); return $ts?date('d/m/Y \à\s H:i',$ts):($s??''); } };
                    ?>
                    <div class="row">
                         <div class="col-12">
                              <div class="card">
                                   <div class="card-header">
                                        <h5 class="card-title mb-0">Tarefas</h5>
                                   </div>
                                   <div class="card-body">
                                        <div class="row">
                                             <div class="col-lg-4 col-md-6">
                                                  <div class="d-flex align-items-center gap-3 mb-3">
                                                       <div class="avatar-sm bg-primary-subtle text-primary rounded d-flex align-items-center justify-content-center">
                                                            <i class="bx bx-task fs-18"></i>
                                                       </div>
                                                       <div>
                                                            <h5 class="mb-1">Atribuídas a você</h5>
                                                            <p class="text-muted mb-0"><?php echo (int)$countAtribuidas ?> tarefas</p>
                                                       </div>
                                                  </div>
                                             </div>
                                             <div class="col-lg-4 col-md-6">
                                                  <div class="d-flex align-items-center gap-3 mb-3">
                                                       <div class="avatar-sm bg-warning-subtle text-warning rounded d-flex align-items-center justify-content-center">
                                                            <i class="bx bx-calendar-event fs-18"></i>
                                                       </div>
                                                       <div>
                                                            <h5 class="mb-1">Agendadas</h5>
                                                            <p class="text-muted mb-0"><?php echo (int)$countAgendadas ?> tarefas</p>
                                                       </div>
                                                  </div>
                                             </div>
                                             <div class="col-lg-4 col-md-6">
                                                  <div class="d-flex align-items-center gap-3 mb-3">
                                                       <div class="avatar-sm bg-success-subtle text-success rounded d-flex align-items-center justify-content-center">
                                                            <i class="bx bx-check-circle fs-18"></i>
                                                       </div>
                                                       <div>
                                                            <h5 class="mb-1">Concluídas</h5>
                                                            <p class="text-muted mb-0"><?php echo (int)$countConcluidas ?> tarefas</p>
                                                       </div>
                                                  </div>
                                             </div>
                                        </div>

                                        <div class="mt-4">
                                             <h6 class="mb-3">Delegadas para você</h6>
                                             <div class="activity-feed">
                                                 <?php if (empty($tasks)): ?>
                                                 <div class="text-muted">Nenhuma tarefa delegada.</div>
                                                 <?php else: foreach ($tasks as $t): ?>
                                                  <div class="d-flex align-items-center gap-3 mb-3">
                                                       <div class="avatar-xs bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center">
                                                            <i class="bx bx-share-alt fs-12"></i>
                                                       </div>
                                                       <div class="flex-grow-1">
                                                            <p class="mb-1">
                                                                Checklist de <strong><?php echo htmlspecialchars($t['equip_nome']) ?></strong>
                                                                delegado por <?php echo htmlspecialchars($t['by'] ?: '—') ?>
                                                                <?php if (!empty($t['reason'])): ?> — Motivo: <?php echo htmlspecialchars($t['reason']) ?><?php endif; ?>
                                                            </p>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <small class="text-muted"><?php echo htmlspecialchars($fmt($t['when'])) ?></small>
                                                                <div>
                                                                    <span class="badge bg-light text-dark me-2">Status: <?php echo htmlspecialchars($t['status']) ?></span>
                                                                    <a href="equipamentos-view.php?id=<?php echo (int)$t['equip_id'] ?>" class="btn btn-sm btn-outline-primary">Abrir</a>
                                                                </div>
                                                            </div>
                                                       </div>
                                                  </div>
                                                 <?php endforeach; endif; ?>
                                             </div>
                                        </div>
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

     <!-- Page Js -->
     <!-- <script src="assets/js/pages/profile.js"></script> -->


</body>

</html>
