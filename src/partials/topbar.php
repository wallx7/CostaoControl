<header class="topbar">
     <div class="container-fluid">
          <div class="navbar-header">
               <div class="d-flex align-items-center">
                    <!-- Menu Toggle Button -->
                    <div class="topbar-item">
                         <button type="button" class="button-toggle-menu me-2">
                              <iconify-icon icon="solar:hamburger-menu-broken" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- Menu Toggle Button -->
                    <div class="topbar-item">
                         <h4 class="fw-bold topbar-button pe-none text-uppercase mb-0"><?php echo  $subTitle ?></h4>
                    </div>
               </div>

               <div class="d-flex align-items-center gap-1">

                    <!-- Theme Color (Light/Dark) -->
                    <div class="topbar-item">
                         <button type="button" class="topbar-button" id="light-dark-mode">
                              <iconify-icon icon="solar:moon-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <?php $uid = $_SESSION['user']['id'] ?? null; if (isset($_GET['clear_notifs'])) { $_SESSION['notif_cleared_at'] = date('c'); } $notifications=[]; if ($uid) { try { require_once __DIR__.'/../services/store.php'; $params=['select'=>'*','usuario_id'=>'eq.'.$uid,'acao'=>'eq.assign_task','order'=>'criado_em.desc','limit'=>10]; if (!empty($_SESSION['notif_cleared_at'])) { $params['criado_em'] = 'gt.' . $_SESSION['notif_cleared_at']; } $notifications = banco_get('logs_atividades', $params); } catch (Exception $e) { $notifications=[]; } } $notifCount = is_array($notifications)?count($notifications):0; ?>
                    <div class="dropdown topbar-item">
                         <button type="button" class="topbar-button position-relative" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <iconify-icon icon="solar:bell-bing-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                              <span class="position-absolute topbar-badge fs-10 translate-middle badge bg-danger rounded-pill"><?php echo (int)$notifCount ?><span class="visually-hidden">não lidas</span></span>
                         </button>
                         <div class="dropdown-menu py-0 dropdown-lg dropdown-menu-end" aria-labelledby="page-header-notifications-dropdown">
                              <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                   <div class="row align-items-center">
                                        <div class="col">
                                             <h6 class="m-0 fs-16 fw-semibold"> Notificações</h6>
                                        </div>
                                        <div class="col-auto"><a href="?clear_notifs=1" class="text-dark text-decoration-underline"><small>Limpar</small></a></div>
                                   </div>
                              </div>
                              <div data-simplebar style="max-height: 280px;">
                                   <?php if (empty($notifications)): ?>
                                   <div class="text-muted px-3 py-2">Sem notificações.</div>
                                   <?php else: foreach ($notifications as $n): $acao=$n['acao']??''; $msgRaw=$n['mensagem']??''; $payload=is_array($msgRaw)?$msgRaw:json_decode((string)$msgRaw,true); $ico=['assign_task'=>'solar:clipboard-text-bold-duotone','checklist_update'=>'solar:checklist-minimalistic-bold-duotone','schedule_set'=>'solar:calendar-bold-duotone'][$acao]??'solar:bell-bing-bold-duotone'; $text=''; if($acao==='assign_task' && is_array($payload)){ $to=$payload['to_name']??('Usuário #'.($payload['to_id']??'')); $reason=trim((string)($payload['reason']??'')); $note=trim((string)($payload['note']??'')); $text='Tarefa atribuída para '.$to.($reason!==''?' — Motivo: '.$reason:'').($note!==''?' — Obs.: '.$note:''); } else { $text=$msgRaw?:ucfirst($acao); } $dt=$n['criado_em']??''; $dtFmt=$dt; try { if ($dt) { $d=new DateTime((string)$dt); $d->setTimezone(new DateTimeZone('America/Sao_Paulo')); $dtFmt=$d->format('d/m/Y H:i'); } } catch (Exception $e) { $ts=strtotime($dt); $dtFmt=$ts?date('d/m/Y H:i',$ts):$dt; } ?>
                                   <a href="javascript:void(0);" class="dropdown-item py-3 border-bottom text-wrap">
                                        <div class="d-flex">
                                             <div class="flex-shrink-0">
                                                  <div class="avatar-sm me-2"><span class="avatar-title bg-soft-primary text-primary fs-20 rounded-circle"><iconify-icon icon="<?php echo $ico ?>"></iconify-icon></span></div>
                                             </div>
                                             <div class="flex-grow-1">
                                                  <p class="mb-0 text-wrap"><?php echo htmlspecialchars($text) ?></p>
                                                  <small class="text-muted"><?php echo htmlspecialchars($dtFmt) ?></small>
                                             </div>
                                        </div>
                                   </a>
                                   <?php endforeach; endif; ?>
                              </div>
                              <div class="text-center py-3"><a href="javascript:void(0);" class="btn btn-primary btn-sm">Ver todas as notificações <i class="bx bx-right-arrow-alt ms-1"></i></a></div>
                         </div>
                    </div>

                    <!-- Theme Setting -->
                    <div class="topbar-item d-none d-md-flex">
                         <button type="button" class="topbar-button" id="theme-settings-btn" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
                              <iconify-icon icon="solar:settings-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- Activity -->
                    <div class="topbar-item d-none d-md-flex">
                         <button type="button" class="topbar-button" id="theme-settings-btn" data-bs-toggle="offcanvas" data-bs-target="#theme-activity-offcanvas" aria-controls="theme-settings-offcanvas">
                              <iconify-icon icon="solar:clock-circle-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                         </button>
                    </div>

                    <!-- User -->
                    <div class="dropdown topbar-item">
                         <a type="button" class="topbar-button" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="d-flex align-items-center">
                                   <?php
                                   $u = $_SESSION['user'] ?? [];
                                   $av = isset($u['avatar']) ? (string)$u['avatar'] : '';
                                   $defaultAv = 'assets/images/costaopadrao.jpg';
                                   if ($av !== '' && preg_match('#^https?://#', $av)) {
                                       // keep URL
                                   } else {
                                       $av = ($av !== '' && file_exists(__DIR__ . '/../' . $av)) ? $av : $defaultAv;
                                   }
                                   ?>
                                   <img class="rounded-circle" width="32" src="<?php echo htmlspecialchars($av) ?>" alt="avatar" onerror="this.src='assets/images/costaopadrao.jpg'">
                              </span>
                         </a>
                         <div class="dropdown-menu dropdown-menu-end">
                              <!-- item-->
                              <?php $u = $_SESSION['user'] ?? ['nome'=>'Usuário']; ?>
                              <h6 class="dropdown-header">Bem-vindo, <?php echo htmlspecialchars($u['nome']) ?>!</h6>
                              <a class="dropdown-item" href="pages-profile.php">
                                   <i class="bx bx-user-circle text-muted fs-18 align-middle me-1"></i><span class="align-middle">Perfil</span>
                              </a>
                              <a class="dropdown-item" href="#" id="toggle-fullscreen">
                                   <i class="bx bx-fullscreen text-muted fs-18 align-middle me-1"></i><span class="align-middle">Tela cheia</span>
                              </a>

                              <div class="dropdown-divider my-1"></div>

                              <a class="dropdown-item text-danger" href="auth-signin.php">
                                   <i class="bx bx-log-out fs-18 align-middle me-1"></i><span class="align-middle">Sair</span>
                              </a>
                         </div>
                    </div>

                    <!-- App Search-->
                    <form class="app-search d-none d-md-block ms-2">
                         <div class="position-relative">
                              <input type="search" class="form-control" placeholder="Buscar..." autocomplete="off" value="">
                              <iconify-icon icon="solar:magnifer-linear" class="search-widget-icon"></iconify-icon>
                         </div>
                    </form>
               </div>
          </div>
     </div>
</header>

<!-- Activity Timeline -->
<div>
     <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-activity-offcanvas" style="max-width: 450px; width: 100%;">
          <div class="d-flex align-items-center bg-primary p-3 offcanvas-header">
               <h5 class="text-white m-0 fw-semibold">Atividades</h5>
               <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>

          <div class="offcanvas-body p-0">
               <?php
               require_once __DIR__.'/../services/store.php';
               $qAction = isset($_GET['acao']) ? trim($_GET['acao']) : '';
               $qUser = isset($_GET['usuario']) ? trim($_GET['usuario']) : '';
               $per = isset($_GET['per']) ? max(5, min(100, (int)$_GET['per'])) : 15;
               $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
               $params = ['select'=>'*','order'=>'criado_em.desc'];
               if ($qAction !== '') { $params['acao'] = 'eq.' . $qAction; }
               if ($qUser !== '') { $params['usuario_id'] = 'eq.' . $qUser; }
               $logs = banco_get('logs_atividades', $params);
               $entries = is_array($logs) ? $logs : [];
               $file = __DIR__.'/../assets/data/activity.json';
               if (file_exists($file)) { $raw = json_decode(file_get_contents($file), true); if (is_array($raw)) $entries = array_merge($entries, $raw); }
               usort($entries, function($a,$b){ return strcmp(($b['criado_em']??''), ($a['criado_em']??'')); });
               $actions=[]; $users=[];
               foreach($entries as $e){ $a=$e['acao']??''; if($a!=='') $actions[$a]=true; $u=$e['usuario_id']??null; if($u!==null) $users[$u]=true; }
               $userNames=[]; foreach (usersList() as $ur) { $userNames[(int)($ur['id']??0)] = $ur['nome'] ?? ('Usuário #' . ($ur['id']??'')); }
               $iconMap=[
                   'collector_add'=>'solar:database-bold-duotone',
                   'collector_update'=>'solar:database-bold-duotone',
                   'assign_task'=>'solar:clipboard-text-bold-duotone',
                   'checklist_update'=>'solar:checklist-minimalistic-bold-duotone',
                   'csv_import'=>'solar:import-bold-duotone',
                   'equipamentoAdd'=>'solar:box-bold-duotone',
                   'termoAdd'=>'solar:document-add-bold-duotone',
                   'termoAssinar'=>'solar:document-bold-duotone',
                   'login'=>'solar:login-3-bold-duotone',
                   'logout'=>'solar:logout-3-bold-duotone',
                   'schedule_set'=>'solar:calendar-bold-duotone',
                   'create'=>'solar:plus-square-bold-duotone',
                   'update'=>'solar:pen-bold-duotone',
                   'delete'=>'solar:trash-bin-minimalistic-2-bold-duotone',
                   'email'=>'solar:letter-bold-duotone',
                   'user_avatar_update'=>'solar:user-bold-duotone'
               ];
               $labelMap=[
                   'collector_add'=>'Coleta: novo equipamento',
                   'collector_update'=>'Coleta: atualização',
                   'assign_task'=>'Tarefa atribuída',
                   'checklist_update'=>'Checklist atualizado',
                   'csv_import'=>'Importação CSV',
                   'equipamentoAdd'=>'Cadastro de equipamento',
                   'termoAdd'=>'Cadastro de termo',
                   'termoAssinar'=>'Termo assinado',
                   'login'=>'Login',
                   'logout'=>'Logout',
                   'schedule_set'=>'Agendamento definido',
                   'create'=>'Cadastro',
                   'update'=>'Atualização',
                   'delete'=>'Exclusão',
                   'email'=>'E-mail enviado',
                   'user_avatar_update'=>'Avatar atualizado'
               ];
               $allowedActions=array_keys($labelMap);
               // Não filtrar por ações conhecidas: exibir qualquer ação, usando rótulos ou o nome cru
               $equipNames=[]; foreach (equipamentosList() as $it) { $equipNames[(int)($it['id']??0)] = $it['nome'] ?? ('#'.($it['id']??'')); }
               $total=count($entries);
               $pages=max(1,(int)ceil($total/$per));
               if($page>$pages) $page=$pages;
               $slice=array_slice($entries, ($page-1)*$per, $per);
               ?>
               <div data-simplebar class="h-100 p-4">
                    <form class="row g-2 mb-3" method="get">
                        <div class="col-6">
                            <select name="acao" class="form-select form-select-sm">
                                <option value="">Todas as ações</option>
                                <?php foreach ($allowedActions as $a): $label = $labelMap[$a] ?? $a; ?><option value="<?php echo htmlspecialchars($a) ?>" <?php echo $qAction===$a?'selected':''; ?>><?php echo htmlspecialchars($label) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-4">
                            <select name="usuario" class="form-select form-select-sm">
                                <option value="">Todos os usuários</option>
                                <?php foreach (array_keys($users) as $uid): $uname=$userNames[(int)$uid] ?? ('Usuário #' . (int)$uid); ?><option value="<?php echo (int)$uid ?>" <?php echo $qUser===(string)$uid?'selected':''; ?>><?php echo htmlspecialchars($uname) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-2">
                            <select name="per" class="form-select form-select-sm">
                                <?php foreach ([10,15,20,30,50] as $opt): ?><option value="<?php echo $opt ?>" <?php echo $per===$opt?'selected':''; ?>><?php echo $opt ?>/página</option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12"><button class="btn btn-sm btn-outline-primary w-100" type="submit">Filtrar</button></div>
                    </form>

                    <div class="position-relative ms-2">
                        <span class="position-absolute start-0  top-0 border border-dashed h-100"></span>
                        <?php if (empty($slice)): ?><div class="text-muted">Sem atividades registradas.</div><?php else: foreach ($slice as $ev): $ico=$iconMap[$ev['acao']??''] ?? null; if(!$ico){ $ent=strtolower($ev['entidade']??''); $ico=(['equipamentos'=>'solar:monitor-bold-duotone','equipamento'=>'solar:monitor-bold-duotone','termos'=>'solar:document-bold-duotone','usuarios'=>'solar:users-group-rounded-bold-duotone','checklists'=>'solar:checklist-minimalistic-bold-duotone'][$ent]??'solar:bell-bing-bold-duotone'); } $label = $labelMap[$ev['acao']??''] ?? ($ev['acao']??''); ?>
                        <div class="position-relative ps-4">
                            <div class="mb-4">
                                <span class="position-absolute start-0 avatar-sm translate-middle-x bg-warning d-inline-flex align-items-center justify-content-center rounded-circle text-dark fs-20"><iconify-icon icon="<?php echo $ico ?>"></iconify-icon></span>
                                <div class="ms-2">
                                    <h6 class="mb-1 text-dark fw-semibold fs-15 lh-base"><?php echo htmlspecialchars($label) ?></h6>
                                    <?php $entRaw=$ev['entidade']??''; $eid=(int)($ev['entidade_id']??0); if ($entRaw==='equipamentos' || $entRaw==='equipamento') { $ename=$equipNames[$eid] ?? ('#'.$eid); ?><p class="mb-1 text-muted"><?php echo htmlspecialchars('Equipamento: '.$ename) ?></p><?php } elseif ($entRaw==='usuarios' || $entRaw==='usuario') { $uname=$userNames[$eid] ?? ('#'.$eid); ?><p class="mb-1 text-muted"><?php echo htmlspecialchars('Usuário: '.$uname) ?></p><?php } else { $entLbl = ['termos'=>'Termo','usuarios'=>'Usuário'][$entRaw] ?? (ucfirst($entRaw?:'Item')); ?><p class="mb-1 text-muted"><?php echo htmlspecialchars($entLbl.': #'.($ev['entidade_id'] ?? '')) ?></p><?php } ?>
                                    <?php if (!empty($ev['usuario_id'])): $uname=$userNames[(int)$ev['usuario_id']] ?? ('Usuário #'.(int)$ev['usuario_id']); ?><p class="mb-1"><small class="text-muted"><?php echo htmlspecialchars($uname) ?></small></p><?php endif; ?>
                <?php $pdRaw=$ev['payload']??($ev['mensagem']??null); $pd=is_array($pdRaw)?$pdRaw:json_decode((string)$pdRaw,true); $detail=''; if (is_array($pd)) { if (($ev['acao']??'')==='checklist_update' && isset($pd['fields'])) { $map=['av'=>'Kaspersky','remote'=>'RustDesk','updates'=>'Atualizações','enc'=>'Criptografia','backup'=>'Backup','tag'=>'Etiqueta']; $names=[]; foreach ((array)$pd['fields'] as $k){ $names[]=$map[$k]??$k; } $detail='Itens: '.implode(', ',$names); } elseif (($ev['acao']??'')==='csv_import') { $detail='Importados '.($pd['imported']??''). ' • Ignorados '.($pd['skipped']??''); } elseif (($ev['acao']??'')==='equipamentoAdd') { $detail='Nome '.($pd['nome']??'').' • Série '.($pd['numero_serie']??'').' • Patrimônio '.($pd['patrimonio']??''); } elseif (($ev['acao']??'')==='schedule_set' && isset($pd['scheduled_at'])) { $detail='Quando: '.$pd['scheduled_at']; } elseif (($ev['acao']??'')==='assign_task') { $to=$pd['to_name']??($userNames[(int)($pd['to_id']??0)] ?? ('Usuário #'.($pd['to_id']??''))); $rs=trim((string)($pd['reason']??'')); $note=trim((string)($pd['note']??'')); $detail='Atribuído para '.$to.($rs!==''?' • Motivo: '.$rs:'').($note!==''?' • Obs.: '.$note:''); } } if ($detail!==''): ?><p class="mb-0"><small class="text-muted"><?php echo htmlspecialchars($detail) ?></small></p><?php endif; ?>
                                    <h6 class="mt-2 text-muted"><?php $dt=$ev['criado_em']??''; $out=$dt; try{ if($dt){ $d=new DateTime((string)$dt); $d->setTimezone(new DateTimeZone('America/Sao_Paulo')); $out=$d->format('d/m/Y H:i'); } } catch(Exception $e){ $ts=strtotime($dt); $out=$ts?date('d/m/Y H:i',$ts):$dt; } echo htmlspecialchars($out); ?></h6>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <?php $base='?'.http_build_query(['acao'=>$qAction,'usuario'=>$qUser,'per'=>$per]); ?>
                    <nav aria-label="Activity pagination" class="mt-2">
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item <?php echo $page<=1?'disabled':''; ?>"><a class="page-link" href="<?php echo $base.'&page='.max(1,$page-1) ?>">Anterior</a></li>
                            <li class="page-item active"><a class="page-link" href="#"><?php echo $page ?></a></li>
                            <li class="page-item <?php echo $page>=$pages?'disabled':''; ?>"><a class="page-link" href="<?php echo $base.'&page='.min($pages,$page+1) ?>">Próximo</a></li>
                        </ul>
                    </nav>
               </div>
               <div data-simplebar class="h-100 p-4 d-none">
                    <div class="position-relative ms-2">
                         <span class="position-absolute start-0  top-0 border border-dashed h-100"></span>
                         <div class="position-relative ps-4">
                              <div class="mb-4">
                                   <span class="position-absolute start-0 avatar-sm translate-middle-x bg-danger d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-20"><iconify-icon icon="iconamoon:folder-check-duotone"></iconify-icon></span>
                                   <div class="ms-2">
                                        <h5 class="mb-1 text-dark fw-semibold fs-15 lh-base">Report-Fix / Update </h5>
                                        <p class="d-flex align-items-center">Add 3 files to <span class=" d-flex align-items-center text-primary ms-1"><iconify-icon icon="iconamoon:file-light"></iconify-icon> Tasks</span></p>
                                        <div class="bg-light bg-opacity-50 rounded-2 p-2">
                                             <div class="row">
                                                  <div class="col-lg-6 border-end border-light">
                                                       <div class="d-flex align-items-center gap-2">
                                                            <i class="bx bxl-figma fs-20 text-red"></i>
                                                            <a href="#!" class="text-dark fw-medium">Concept.fig</a>
                                                       </div>
                                                  </div>
                                                  <div class="col-lg-6">
                                                       <div class="d-flex align-items-center gap-2">
                                                            <i class="bx bxl-file-doc fs-20 text-success"></i>
                                                            <a href="#!" class="text-dark fw-medium">larkon.docs</a>
                                                       </div>
                                                  </div>
                                             </div>
                                        </div>
                                        <h6 class="mt-2 text-muted">Monday , 4:24 PM</h6>
                                   </div>
                              </div>
                         </div>
                         <div class="position-relative ps-4">
                              <div class="mb-4">
                                   <span class="position-absolute start-0 avatar-sm translate-middle-x bg-success d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-20"><iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon></span>
                                   <div class="ms-2">
                                        <h5 class="mb-1 text-dark fw-semibold fs-15 lh-base">Project Status
                                        </h5>
                                        <p class="d-flex align-items-center mb-0">Marked<span class=" d-flex align-items-center text-primary mx-1"><iconify-icon icon="iconamoon:file-light"></iconify-icon> Design </span> as <span class="badge bg-success-subtle text-success px-2 py-1 ms-1"> Completed</span></p>
                                        <div class="d-flex align-items-center gap-3 mt-1 bg-light bg-opacity-50 p-2 rounded-2">
                                             <a href="#!" class="fw-medium text-dark">UI/UX Figma Design</a>
                                             <div class="ms-auto">
                                                  <a href="#!" class="fw-medium text-primary fs-18" data-bs-toggle="tooltip" data-bs-title="Download" data-bs-placement="bottom"><iconify-icon icon="iconamoon:cloud-download-duotone"></iconify-icon></a>
                                             </div>
                                        </div>
                                        <h6 class="mt-3 text-muted">Monday , 3:00 PM</h6>
                                   </div>
                              </div>
                         </div>
                         <div class="position-relative ps-4">
                              <div class="mb-4">
                                   <span class="position-absolute start-0 avatar-sm translate-middle-x bg-primary d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-16">UI</span>
                                   <div class="ms-2">
                                        <h5 class="mb-1 text-dark fw-semibold fs-15">Larkon Application UI v2.0.0 <span class="badge bg-primary-subtle text-primary px-2 py-1 ms-1"> Latest</span>
                                        </h5>
                                        <p>Get access to over 20+ pages including a dashboard layout, charts, kanban board, calendar, and pre-order E-commerce & Marketing pages.</p>
                                        <div class="mt-2">
                                             <a href="#!" class="btn btn-light btn-sm">Download Zip</a>
                                        </div>
                                        <h6 class="mt-3 text-muted">Monday , 2:10 PM</h6>
                                   </div>
                              </div>
                         </div>
                         <div class="position-relative ps-4">
                              <div class="mb-4">
                                   <span class="position-absolute start-0 translate-middle-x bg-success bg-gradient d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-20"><img src="assets/images/users/avatar-7.jpg" alt="avatar-5" class="avatar-sm rounded-circle"></span>
                                   <div class="ms-2">
                                        <h5 class="mb-0 text-dark fw-semibold fs-15 lh-base">Alex Smith Attached Photos
                                        </h5>
                                        <div class="row g-2 mt-2">
                                             <div class="col-lg-4">
                                                  <a href="#!">
                                                       <img src="assets/images/small/img-6.jpg" alt="" class="img-fluid rounded">
                                                  </a>
                                             </div>
                                             <div class="col-lg-4">
                                                  <a href="#!">
                                                       <img src="assets/images/small/img-3.jpg" alt="" class="img-fluid rounded">
                                                  </a>
                                             </div>
                                             <div class="col-lg-4">
                                                  <a href="#!">
                                                       <img src="assets/images/small/img-4.jpg" alt="" class="img-fluid rounded">
                                                  </a>
                                             </div>
                                        </div>
                                        <h6 class="mt-3 text-muted">Monday 1:00 PM</h6>
                                   </div>
                              </div>
                         </div>
                         <div class="position-relative ps-4">
                              <div class="mb-4">
                                   <span class="position-absolute start-0 translate-middle-x bg-success bg-gradient d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-20"><img src="assets/images/users/avatar-6.jpg" alt="avatar-5" class="avatar-sm rounded-circle"></span>
                                   <div class="ms-2">
                                        <h5 class="mb-0 text-dark fw-semibold fs-15 lh-base">Rebecca J. added a new team member
                                        </h5>
                                        <p class="d-flex align-items-center gap-1"><iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success"></iconify-icon> Added a new member to Front Dashboard</p>
                                        <h6 class="mt-3 text-muted">Monday 10:00 AM</h6>
                                   </div>
                              </div>
                         </div>
                         <div class="position-relative ps-4">
                              <div class="mb-4">
                                   <span class="position-absolute start-0 avatar-sm translate-middle-x bg-warning d-inline-flex align-items-center justify-content-center rounded-circle text-light fs-20"><iconify-icon icon="iconamoon:certificate-badge-duotone"></iconify-icon></span>
                                   <div class="ms-2">
                                        <h5 class="mb-0 text-dark fw-semibold fs-15 lh-base">Achievements
                                        </h5>
                                        <p class="d-flex align-items-center gap-1 mt-1">Earned a <iconify-icon icon="iconamoon:certificate-badge-duotone" class="text-danger fs-20"></iconify-icon>" Best Product Award"</p>
                                        <h6 class="mt-3 text-muted">Monday 9:30 AM</h6>
                                   </div>
                              </div>
                         </div>
                    </div>
                    <a href="#!" class="btn btn-outline-dark w-100">View All</a>
               </div>
          </div>
     </div>
</div>

<!-- Right Sidebar (Theme Settings) -->
<?php include 'right-sidebar.php' ?>
