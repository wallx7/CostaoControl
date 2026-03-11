<?php include 'partials/main.php' ?>
<?php require_once 'services/store.php'; ?>

<head>
     <?php
    $subTitle = "Itens do Inventário";
    include 'partials/title-meta.php'; ?>

       <?php include 'partials/head-css.php' ?>
</head>

<body>

     <!-- START Wrapper -->
     <div class="wrapper">

          <?php 
    $subTitle = "Itens do Inventário";
    include 'partials/topbar.php'; ?>
<?php include 'partials/main-nav.php'; ?>

          <!-- ==================================================== -->
          <!-- Start right Content here -->
          <!-- ==================================================== -->
          <div class="page-content">

               <!-- Start Container Fluid -->
               <div class="container-xxl">

                    <div class="row">
<?php $itemsAll = equipamentosSystemList(); $total = equipamentosSystemCount(); $countUso=0; $countManut=0; $countDisp=0; foreach($itemsAll as $it){ $st=$it['status']??''; if($st==='Em uso'||$st==='em_uso') $countUso++; elseif($st==='Manutenção'||$st==='em_manutencao') $countManut++; elseif($st==='Disponível'||$st==='disponivel') $countDisp++; } ?>
                         <div class="col-md-6 col-xl-3">
                              <div class="card">
                                   <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                             <div>
                                                  <h4 class="card-title mb-2 d-flex align-items-center gap-2">Total de Equipamentos</h4>
                                                  <p class="text-muted fw-medium fs-22 mb-0"><?php echo $total ?> <span class="fs-12">(itens)</span></p>
                                             </div>
                                             <div>
                                                  <div class="avatar-md bg-primary bg-opacity-10 rounded">
                                                       <iconify-icon icon="solar:monitor-bold-duotone" class="fs-32 text-primary avatar-title"></iconify-icon>
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
                                                  <h4 class="card-title mb-2 d-flex align-items-center gap-2">Em uso</h4>
                                                  <p class="text-muted fw-medium fs-22 mb-0"><?php echo $countUso ?> <span class="fs-12">(itens)</span></p>
                                             </div>
                                             <div>
                                                  <div class="avatar-md bg-primary bg-opacity-10 rounded">
                                                       <iconify-icon icon="solar:user-bold-duotone" class="fs-32 text-primary avatar-title"></iconify-icon>
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
                                                  <h4 class="card-title mb-2 d-flex align-items-center gap-2">Em manutenção</h4>
                                                  <p class="text-muted fw-medium fs-22 mb-0"><?php echo $countManut ?> <span class="fs-12">(itens)</span></p>
                                             </div>
                                             <div>
                                                  <div class="avatar-md bg-primary bg-opacity-10 rounded">
                                                       <iconify-icon icon="solar:bag-cross-broken" class="fs-32 text-primary avatar-title"></iconify-icon>
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
                                                  <h4 class="card-title mb-2 d-flex align-items-center gap-2">Disponíveis</h4>
                                                  <p class="text-muted fw-medium fs-22 mb-0"><?php echo $countDisp ?> <span class="fs-12">(itens)</span></p>
                                             </div>
                                             <div>
                                                  <div class="avatar-md bg-primary bg-opacity-10 rounded">
                                                       <iconify-icon icon="solar:users-group-two-rounded-broken" class="fs-32 text-primary avatar-title"></iconify-icon>
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
                                             <h4 class="card-title">Itens do Inventário</h4>
                                        </div>
                                        <div class="dropdown">
                                             <form class="d-flex gap-2" method="get">
                                                  <input type="text" name="q" class="form-control form-control-sm" placeholder="Buscar por nome, série ou patrimônio" value="<?php echo htmlspecialchars($_GET['q'] ?? '') ?>">
                                                  <button class="btn btn-sm btn-outline-light rounded" type="submit">Filtrar</button>
                                             </form>
                                        </div>
                                   </div>
                                   <div>
                                        <div class="table-responsive">
                                             <table class="table align-middle mb-0 table-hover table-centered">
                                                  <thead class="bg-light-subtle">
                                                       <tr>
                                                            <th style="width: 20px;">
                                                                 <div class="form-check">
                                                                      <input type="checkbox" class="form-check-input" id="customCheck1">
                                                                      <label class="form-check-label" for="customCheck1"></label>
                                                                 </div>
                                                            </th>
                                                            <th>ID</th>
                                                            <th>Nome</th>
                                                            <th>Tipo</th>
                                                            <th>Localização</th>
                                                            <th>Status</th>
                                                            <th>Nº Série</th>
                                                            <th>Patrimônio</th>
                                                            <th>Ações</th>
                                                       </tr>
                                                  </thead>
                                                  <tbody>
                                                       <?php $q = isset($_GET['q']) ? trim($_GET['q']) : ''; $page = max(1, (int)($_GET['page'] ?? 1)); $pp = max(5, (int)($_GET['pp'] ?? 10)); $offset = ($page-1)*$pp; $itemsPage = equipamentosList($q, '', '', '', '', '', $pp, $offset); $list = is_array($itemsPage) ? $itemsPage : []; ?>
                                                       <?php foreach ($list as $r): ?>
                                                       <tr>
                                                            <td>
                                                                 <div class="form-check">
                                                                      <input type="checkbox" class="form-check-input">
                                                                 </div>
                                                            </td>
                                                            <td>#<?php echo (int)($r['id'] ?? 0) ?></td>
                                                            <td><?php echo htmlspecialchars($r['nome'] ?? '') ?></td>
                                                            <td><?php echo htmlspecialchars($r['tipo'] ?? '') ?></td>
                                                            <td><?php echo htmlspecialchars($r['localizacao'] ?? '') ?></td>
                                                            <td><?php echo htmlspecialchars($r['status'] ?? '') ?></td>
                                                            <td><?php echo htmlspecialchars($r['numero_serie'] ?? '') ?></td>
                                                            <td><?php echo htmlspecialchars($r['patrimonio'] ?? '') ?></td>
                                                            <td>
                                                                 <div class="d-flex gap-2">
                                                                      <a href="equipamentos-view.php?id=<?php echo (int)($r['id'] ?? 0) ?>" class="btn btn-light btn-sm"><iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon></a>
                                                                      <a href="equipamentos-edit.php?id=<?php echo (int)($r['id'] ?? 0) ?>" class="btn btn-soft-primary btn-sm"><iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon></a>
                                                                      <a href="#" class="btn btn-soft-danger btn-sm" onclick="alert('Remover item ainda não implementado')"><iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon></a>
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
                                        <?php $totalFiltered = count(equipamentosList($q)); $totalPages = max(1, (int)ceil($totalFiltered / $pp)); $prev = max(1, $page-1); $next = min($totalPages, $page+1); $base = '?q=' . urlencode($q) . '&pp=' . (int)$pp . '&page='; ?>
                                        <nav aria-label="Page navigation">
                                             <ul class="pagination justify-content-end mb-0">
                                                  <li class="page-item <?php echo $page<=1?'disabled':'' ?>"><a class="page-link" href="<?php echo $base . $prev ?>">Anterior</a></li>
                                                  <?php for($p=max(1,$page-2); $p<=min($totalPages,$page+2); $p++): ?>
                                                  <li class="page-item <?php echo $p===$page?'active':'' ?>"><a class="page-link" href="<?php echo $base . $p ?>"><?php echo $p ?></a></li>
                                                  <?php endfor; ?>
                                                  <li class="page-item <?php echo $page>=$totalPages?'disabled':'' ?>"><a class="page-link" href="<?php echo $base . $next ?>">Próxima</a></li>
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

</body>

</html>
